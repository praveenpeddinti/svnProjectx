import {Injectable} from '@angular/core';
declare var jQuery:any;

@Injectable()
export class MentionService
{
    load_atwho(editor, at_config)
    {
        if (editor.mode != 'source') 
        {
                editor.document.getBody().$.contentEditable = true;
                jQuery(editor.document.getBody().$).atwho(at_config);
        }
            // Source mode when switching from WYSIWYG
        else{
                jQuery(editor.container.$).find(".cke_source").atwho(at_config);
            }
    }
}