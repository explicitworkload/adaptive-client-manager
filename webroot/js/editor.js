/*
 * Rich-text editor for email bodies and email templates (Jodit 4, MIT licence).
 * Replaces TinyMCE 3. Load after the Jodit script (webroot/vendor/jodit-VERSION/jodit.min.js).
 *
 *   var editor = acmEditor('#email');
 *   editor.value                // current HTML
 *   editor.s.insertHTML('...')  // insert at the cursor
 */
window.acmEditor = function (selector) {
    return Jodit.make(selector, {
        buttons: [
            'bold', 'italic', 'underline', '|', 'left', 'center', 'right', 'font', 'fontsize', '\n',
            'ul', 'ol', '|', 'outdent', 'indent', '|', 'undo', 'redo', '|', 'link', 'image', '|', 'brush', '|', 'source'
        ],
        toolbarAdaptive: false,
        minHeight: 300,
        showCharsCounter: false,
        showWordsCounter: false,
        showXPathInStatusbar: false,
        askBeforePasteHTML: false,
        askBeforePasteFromWord: false,
        // Images are inserted by URL; email clients often block embedded (base64) images.
        uploader: { insertImageAsBase64URI: false },
        beautifyHTML: false
    });
};
