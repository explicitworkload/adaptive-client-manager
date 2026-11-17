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
     * Replaces the legacy webroot/post_file.php, which accepted uploads from anyone.
     */
    public function uploadLogo(): Response
    {
        $this->request->allowMethod(['post']);
        $file = $this->request->getUploadedFile('logo');
        if (!$file instanceof UploadedFileInterface || $file->getError() !== UPLOAD_ERR_OK) {
            $this->Flash->error('Please choose a PNG image to upload.');

            return $this->redirect(['action' => 'editConfig', SettingsTable::ID]);
        }
        $tmp = tempnam(TMP, 'logo');
        $file->moveTo($tmp);
        $info = @getimagesize($tmp);
        if ($info === false || $info[2] !== IMAGETYPE_PNG) {
            unlink($tmp);
            $this->Flash->error('Only PNG files are allowed!');
        } else {
            $logo = WWW_ROOT . 'img' . DS . 'logo.png';
            rename($tmp, $logo);
            // tempnam() creates the file as 0600; the web server may read static
            // files as a different user, so make it world-readable like the rest.
            chmod($logo, 0644);
            $this->Flash->success('File was uploaded successfully!');
        }

        return $this->redirect(['action' => 'editConfig', SettingsTable::ID]);
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
