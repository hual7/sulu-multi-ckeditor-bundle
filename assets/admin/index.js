// @flow
import {textEditorRegistry, fieldRegistry} from 'sulu-admin-bundle/containers';
import {initializer} from 'sulu-admin-bundle/services';
import {setEditorConfigs} from './config';
import CKEditor5ConfigurableAdapter from './adapters/CKEditor5Configurable';
import ConfigurableTextEditor from './fields/ConfigurableTextEditor';

initializer.addUpdateConfigHook('akawaka_sulu_multi_text_editor', (config, initialized) => {
    setEditorConfigs(config.configs || {});
    if (initialized) {
        return;
    }
});

textEditorRegistry.add('ckeditor5_configurable', CKEditor5ConfigurableAdapter);
fieldRegistry.add('configurable_text_editor', ConfigurableTextEditor);

// Remap schemaType for Sulu AI Writing Assistant compatibility.
// AiApplication.js only recognises 'text_line', 'text_area', and 'text_editor'.
// 'configurable_text_editor' is functionally identical to 'text_editor', so intercept
// the sulu.focus event in capture phase before AiApplication reads it.
document.addEventListener('sulu.focus', function(event) {
    if (event.detail && event.detail.schemaType === 'configurable_text_editor') {
        event.detail.schemaType = 'text_editor';
    }
}, true);
