import {Injectable,Inject} from '@angular/core';
import { AjaxService } from '../ajax/ajax.service';
import {Headers,Http} from '@angular/http';
import { GlobalVariable } from '../../app/config';
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
   
    // this_obj:AjaxService;
    mention:any=[];
    constructor(@Inject(AjaxService) this_obj:AjaxService)
    {
    //    this.this_obj=this_obj;
    }

    initialize_editor(element,options,obj)
    {
        var mention_data;
        if(options=='keyup' && obj!=null)
        {
            console.log("first if");
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
                                console.log("===In users=="+keyword);
                                var params={search_term:keyword};
                                var getAllData=  JSON.parse(localStorage.getItem('user'));
                                if(getAllData != null){
                                    params["userInfo"] = getAllData;
                                    params["projectId"] = localStorage.getItem('ProjectId');
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
                                            console.log("==Data=="+JSON.stringify(data));
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
                                
                                //return this.mentions;
                                
                            },        
                            search: function (keyword, callback) {
                                if(keyword.length>0)
                                {
                                    this.users(keyword,callback);

                                }
                            
                            },
                            template: function (item) {
                                console.log("==Profile==**"+item.Profile);
                               return '<div value="'+item.name + '" name="'+item.name+ '"><img width="20" height="20" src="' + item.Profile + '"/>&nbsp;'+item.name+'</div>';
                            },
                            content: function (item) {
                                console.log("===Item=="+item);
                                    return '@' + item.name;
                                    //return '<div value="'+item.name + '" name="'+item.name+ '"><img width="20" height="20" src="http://10.10.73.77"'+item.Profile+'"/>'+item.name+'</div>';
                                    
                                    }    
                        },
                    callbacks: {
                    onKeyup: function(e) {
                         var editor=jQuery('#'+element).summernote('code');
                         editor=jQuery(editor).text().trim();
                         if(editor!='')
                         {
                            obj.form['description']= jQuery('#'+element).summernote('code');
                            console.log(obj.form['description']);
                         }
                         else
                         {
                             obj.form['description']='';
                         }

                    }},
                    disableDragAndDrop:true,

            }
            );
        }
        else
        {
            console.log("==else==");
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
                                console.log("===In users=="+keyword);
                                var params={search_term:keyword};
                                var getAllData=  JSON.parse(localStorage.getItem('user'));
                                if(getAllData != null){
                                    params["userInfo"] = getAllData;
                                    params["projectId"] = localStorage.getItem('ProjectId');
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
                                            console.log("==Data=="+JSON.stringify(data));
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
                                
                                //return this.mentions;
                                
                            },        
                            search: function (keyword, callback) {
                                if(keyword.length>0)
                                {
                                    this.users(keyword,callback);

                                }
                            
                            },
                            template: function (item) {
                                console.log("==Profile=="+item.Profile);
                               return '<div value="'+item.name + '" name="'+item.name+ '"><img width="20" height="20" src="' + item.Profile + '"/>&nbsp;'+item.name+'</div>';
                            },
                            content: function (item) {
                                console.log("===Item=="+item);
                                    return '@' + item.name;
                                    //return '<div value="'+item.name + '" name="'+item.name+ '"><img width="20" height="20" src="http://10.10.73.77"'+item.Profile+'"/>'+item.name+'</div>';
                                    
                                    }    
                        },
                    disableDragAndDrop:true,
                    
                }
            );
        }
    }
}

// callbacks: {
                    // onKeyup: function(e) {
                    //     if (e.keyCode == 13) 
                    //     {
                    //         if(jQuery('#'+element).summernote('isEmpty'))
                    //         {
                    //             jQuery('.note-editable').html('');
                    //         }
                    //     }

                    //     if(jQuery('#'+element).summernote('isEmpty'))
                    //     {
                    //         jQuery('.note-editable').html('');
                    //     }
                    // }},