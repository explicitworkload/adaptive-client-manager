<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $template
 */
?>
<h3>Edit Email Templates</h3>

<p>You can edit the email template for Client Quotes below. There are a number of shortcodes available that can be used to automatically generate relevant information in the email. Shortcodes can be used by typing them in manually or clicking on them in the form below.</p>

<?= $this->element('email_template_form', [
    'template' => $template,
    'kind' => 'quote',
    'formId' => 'EmailTemplateEditClientQuoteForm',
    'url' => ['controller' => 'EmailTemplates', 'action' => 'editClientQuote'],
    'heading' => 'Client Quote Email Template',
]) ?>
