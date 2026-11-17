<?php
/**
 * Generic edit action (/email_templates/edit/{id}); no legacy view existed for it.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $template
 */
use App\Model\Table\EmailTemplatesTable;

$kind = match ($template->id) {
    EmailTemplatesTable::CLIENT_INVOICE => 'invoice',
    EmailTemplatesTable::CLIENT_QUOTE => 'quote',
    default => null,
};
?>
<h3>Edit Email Templates</h3>

<p>You can edit the <?= h($template->name) ?> email template below. There are a number of shortcodes available that can be used to automatically generate relevant information in the email. Shortcodes can be used by typing them in manually or clicking on them in the form below.</p>

<?= $this->element('email_template_form', [
    'template' => $template,
    'kind' => $kind,
    'formId' => 'EmailTemplateEditForm',
    'url' => ['controller' => 'EmailTemplates', 'action' => 'edit', $template->id],
    'heading' => $template->name . ' Email Template',
]) ?>
