<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\SettingsTable;
use App\Service\ThemeCss;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @property \App\Model\Table\SettingsTable $Settings
 */
class SettingsController extends AppController
{
    private const CONFIG_FIELDS = [
        'company_name', 'company_phone', 'company_email', 'company_address_line_1',
        'company_address_line_2', 'company_town', 'company_city', 'company_state',
        'company_zip', 'custom_1', 'payment_info', 'currency',
    ];

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'settings');
        $this->requireAdmin($event, 'You Are Not Authorised To Access Settings');
        $this->viewBuilder()->setLayout('settings');
    }

    /** Company details. The id argument is accepted for legacy URLs (/settings/edit_config/1). */
    public function editConfig(?string $id = null): ?Response
    {
        return $this->editSettings(self::CONFIG_FIELDS, 'editConfig', 'Unable to update your settings');
    }

    /** Theme colour: regenerates webroot/css/dead_pixel.css from dp_template.css. */
    public function editTheme(?string $id = null): ?Response
    {
        return $this->editSettings(['primary_color'], 'editTheme', 'Unable To Update Your Settings', function ($setting): void {
            ThemeCss::build((string)$setting->primary_color);
        });
    }

    /**
     * Replace the company logo (webroot/img/logo.png).
     *
     * The settings page uploads in the background (XMLHttpRequest) and gets a
     * JSON reply ({"ok": bool, "message": string}); a plain form post (no
     * JavaScript) gets a flash message and a redirect instead.
     * Replaces the legacy webroot/post_file.php, which accepted uploads from anyone.
     */
    public function uploadLogo(): Response
    {
        $this->request->allowMethod(['post']);
        [$ok, $message] = $this->saveUploadedLogo();

        if ($this->request->is('ajax')) {
            return $this->response
                ->withType('application/json')
                ->withStatus($ok ? 200 : 422)
                ->withStringBody((string)json_encode(['ok' => $ok, 'message' => $message]));
        }
        $ok ? $this->Flash->success($message) : $this->Flash->error($message);

        return $this->redirect(['action' => 'editConfig', SettingsTable::ID]);
    }

    /**
     * @return array{0: bool, 1: string} success flag and a message for the user
     */
    private function saveUploadedLogo(): array
    {
        $file = $this->request->getUploadedFile('logo');
        if (!$file instanceof UploadedFileInterface || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [false, 'Please choose a PNG image to upload.'];
        }
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return [false, $this->uploadErrorMessage($file->getError())];
        }

        $tmp = tempnam(TMP, 'logo');
        try {
            if ($tmp === false) {
                throw new \RuntimeException('cannot create a temporary file in ' . TMP);
            }
            $file->moveTo($tmp);
            $info = @getimagesize($tmp);
            if ($info === false || $info[2] !== IMAGETYPE_PNG) {
                return [false, 'Only PNG files are allowed!'];
            }
            $this->installLogo($tmp, WWW_ROOT . 'img' . DS . 'logo.png');

            return [true, 'File was uploaded successfully!'];
        } catch (\Throwable $e) {
            $this->log('Logo upload failed: ' . $e->getMessage(), 'error');

            return [false, 'The logo could not be saved: ' . str_replace(ROOT . DS, '', $e->getMessage())
                . '. Check that webroot/img/ and webroot/img/logo.png are writable by PHP.'];
        } finally {
            if (is_string($tmp) && is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }

    /**
     * Put the uploaded PNG in place, verifying each step.
     */
    private function installLogo(string $source, string $target): void
    {
        $expected = md5_file($source);
        if (is_file($target) && !is_writable($target) && !is_writable(dirname($target))) {
            throw new \RuntimeException(sprintf('%s is not writable', $target));
        }
        // rename() is atomic; copy() is the fallback when rename isn't allowed
        // (e.g. across filesystems, or the existing file can't be replaced).
        if (!@rename($source, $target) && !@copy($source, $target)) {
            $error = error_get_last()['message'] ?? 'unknown error';
            throw new \RuntimeException(sprintf('cannot write %s (%s)', $target, $error));
        }
        // tempnam() creates files as 0600; the web server may read static files
        // as a different user, so make the logo world-readable like the rest.
        @chmod($target, 0644);
        clearstatcache(true, $target);
        if (md5_file($target) !== $expected) {
            throw new \RuntimeException(sprintf('%s was not updated', $target));
        }
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That file is larger than the server allows (upload_max_filesize in PHP settings).',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary folder for uploads (upload_tmp_dir in PHP settings).',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded file to its temporary folder.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension on the server blocked the upload.',
            default => 'The upload failed (error ' . $code . ').',
        };
    }

    /**
     * @param list<string> $fields
     * @param callable(\App\Model\Entity\Setting): void|null $afterSave
     */
    private function editSettings(array $fields, string $action, string $failMessage, ?callable $afterSave = null): ?Response
    {
        $setting = $this->Settings->current();
        if ($this->request->is(['post', 'put', 'patch'])) {
            $setting = $this->Settings->patchEntity($setting, $this->request->getData(), ['fields' => $fields]);
            if ($this->Settings->save($setting)) {
                if ($afterSave) {
                    $afterSave($setting);
                }
                $this->Flash->success('Your Settings Have Been Updated');

                return $this->redirect(['action' => $action, SettingsTable::ID]);
            }
            $this->Flash->error($failMessage);
        }
        $this->set('setting', $setting);

        return null;
    }
}
