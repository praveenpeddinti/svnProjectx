import {Injectable,Inject} from '@angular/core';
import { AjaxService } from '../ajax/ajax.service';
import {Headers,Http} from '@angular/http';
import { GlobalVariable } from '../../app/config';
import {SharedService} from '../services/shared.service';
declare var jQuery:any;
declare var jstz:any;
/**
 * @author:Ryan
 * @description: This is used for initializing the summernote editor with @mentions
 * @param: element,options
 */
 @Injectable()
 export class SummerNoteEditorService
 {

     mention:any=[];
     constructor(@Inject(AjaxService) this_obj:AjaxService,private sharedService:SharedService)
     {
     }

     initialize_editor(element,options,obj)
     {
         var mention_data;
         var thisObj= this;
         if(options=='keyup' && obj!=null)
         {
             jQuery('#'+element).summernote(
             {
                 height:200,
                 toolbar: [
                 ['style', ['bold', 'italic', 'underline', 'clear']],
                 ['para', ['ul', 'ol']],
                 ['link', ['linkDialogShow', 'unlink']]
                 ],
                 hint: {
                     mentions:[],
                     match: /\B@(\w*)$/,
                     users:function(keyword,callback)
                     {
                         var params={search_term:keyword};
                         var getAllData=  JSON.parse(localStorage.getItem('user'));
                         if(getAllData != null){
                             params["userInfo"] = getAllData;
                                    params["projectId"] = obj.projectId;//parseInt(localStorage.getItem('ProjectId'));
                                    params["timeZone"] = jstz.determine_timezone().name();
                                }
                                var url=GlobalVariable.BASE_API_URL+"story/get-collaborators";
                                jQuery.ajax(
                                {
                                    url:url,
                                    type:'POST',
                                    data:JSON.stringify(params),
                                    async:true
                                }).done(function(data)
                                {
                                    if(data.statusCode!=200){
                                        thisObj.sharedService.setToasterValue(data.message);
                                    }
                                    var mention_list=[];
                                    for(let i in data.data)
                                    {
                                        mention_list.push({'name':data.data[i].Name,'Profile':data.data[i].ProfilePic});
                                    }
                                           
                                    this.mentions=mention_list;
                                    callback(jQuery.grep(this.mentions, function (item) {
                                        // console.log(item.name.toLowerCase().indexOf(keyword.toLowerCase()));
                                        // return item.name.indexOf(keyword) == 0; //old-one
                                        return item.name.toLowerCase().indexOf(keyword.toLowerCase()) == 0; //Edited@waheed to avoid case sensitive in autocomplete. 
                                    }));

                                });
                                
                                
                            },        
                            search: function (keyword, callback) {
                                if(keyword.length>0)
                                {
                                    this.users(keyword,callback);

                                }

                            },
                            template: function (item) {
                                return '<div value="'+item.name + '" name="'+item.name+ '"><img width="20" height="20" src="' + item.Profile + '"/>&nbsp;'+item.name+'</div>';
                            },
                            content: function (item) {
                                return '@' + item.name;

                            }    
                        },
                        callbacks: {
                            onKeyup: function(e) {
                                this.summernoteLength=false; 
                                var editor=jQuery('#'+element).summernote('code');
                         if(!(editor.indexOf("<p>")>-1)){ //added for default unexpected behaviour of editor
                             editor="<p>"+editor+"</p>";
                         }
                         editor = editor.replace(/^(<p>(<br>)*\s*(<br>)*<\/p>)*(<br>)*|(<p>(<br>)*\s*(<br>)*<\/p>)*(<br>)*$/gi, "");
                         if(obj.formB != undefined){
                             obj.formB['description']=editor;
                         }
                         if(element=="summernote"){ // added to Fix Padmaja's Issues/Conflicts
                             editor=jQuery(editor).text().trim();
                     }
                     if(obj.form != undefined){
                         obj.form['description']=editor;
                         if(editor!='')
                         {
                             obj.form['description']=editor;
                         }
                         else
                         {
                             obj.form['description']='';
                         }
                     }

                 }},
                 disableDragAndDrop:true,

             }
             );
}
else
{
    jQuery("#"+element).summernote(
    {
        height:200,
        toolbar: [
        ['style', ['bold', 'italic', 'underline', 'clear']],
        ['para', ['ul', 'ol']],
        ['link', ['linkDialogShow', 'unlink']]
        ],                   

        hint: {
            mentions:[],
            match: /\B@(\w*)$/,
            users:function(keyword,callback)
            {
                var params={search_term:keyword};
                var getAllData=  JSON.parse(localStorage.getItem('user'));
                if(getAllData != null){
                    params["userInfo"] = getAllData;
                                    params["projectId"] = obj.projectId;//localStorage.getItem('ProjectId');
                                    params["timeZone"] = jstz.determine_timezone().name();
                                }
                                var url=GlobalVariable.BASE_API_URL+"story/get-collaborators";
                                jQuery.ajax(
                                {
                                    url:url,
                                    type:'POST',
                                    data:JSON.stringify(params),
                                    async:true
                                }).done(function(data)
                                {
                                    if(data.statusCode!=200){
                                        thisObj.sharedService.setToasterValue(data.message);
                                    }
                                    var mention_list=[];
                                    for(let i in data.data)
                                    {
                                        mention_list.push({'name':data.data[i].Name,'Profile':data.data[i].ProfilePic});
                                    }

                                    this.mentions=mention_list;
                                    callback(jQuery.grep(this.mentions, function (item) {
                                        return item.name.indexOf(keyword) == 0;
                                    }));

                                });
                                
                                
                            },        
                            search: function (keyword, callback) {
                                if(keyword.length>0)
                                {
                                    this.users(keyword,callback);

                                }

                            },
                            template: function (item) {
                                return '<div value="'+item.name + '" name="'+item.name+ '"><img width="20" height="20" src="' + item.Profile + '"/>&nbsp;'+item.name+'</div>';
                            },
                            content: function (item) {
                                return '@' + item.name;

                            }    
                        },
                        disableDragAndDrop:true,

                    }
                    );
}
}
}

