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
   
    // this_obj:AjaxService;
    mention:any=[];
    constructor(@Inject(AjaxService) this_obj:AjaxService,private sharedService:SharedService)
    {
    //    this.this_obj=this_obj;
    }

    initialize_editor(element,options,obj)
    {
        var mention_data;
        var thisObj= this;
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
                        //  alert("333"+this.summernoteLength);
                         this.summernoteLength=false; 
                        //  alert("444444444444444444"+this.summernoteLength);
                        // console.log(element+"==========Iddddd===========");
                         var editor=jQuery('#'+element).summernote('code');
                         if(!(editor.indexOf("<p>")>-1)){
                              editor="<p>"+editor+"</p>";
                          }
                           console.log(editor+"=====**======1111111111=");
                        editor = editor.replace(/^(<p>(<br>)*\s*(<br>)*<\/p>)*(<br>)*|(<p>(<br>)*\s*(<br>)*<\/p>)*(<br>)*$/gi, "");
                          console.log(editor+"=====**=======");
                         if(obj.formB != undefined){
                             obj.formB['description']=editor;
                         }
                         //editor=jQuery(editor).text().trim();
                        //  console.log(obj.formB['description']+"=-=-=-=-=-");
                         if(obj.form != undefined){
                            obj.form['description']=editor;
                            if(editor!='')
                            {
                                obj.form['description']=editor;
                            //  obj.form['description']= jQuery('#'+element).summernote('code');
                              //  alert(obj.form['description']+"===================");
                            }
                            else
                            {
                                console.log("cmoing here******");
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