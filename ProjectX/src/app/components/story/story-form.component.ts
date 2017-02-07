import { Component } from '@angular/core';
import { StoryService} from '../../services/story.service';

@Component({
    selector: 'story-form',
    templateUrl: 'story-form.html',
    providers: [StoryService]     
    	
})

export class StoryComponent {
    public test="Anand";
    public storyFormData=[];
    public storyData={};
    public form={};
    constructor( private _service: StoryService) { }

    ngOnInit() {
     
        this._service.getStoryFields(1,(response)=>{
              let jsonForm={};
              let DefaultValue;
             // console.log("new_data"+JSON.stringify(response));
              if(response.statusCode==200){
                  response.data.story_fields.forEach(element => {
                    var  item = element.Field_Name;
                    
                    if(element.Type == 5){
                        element.DefaultValue=new Date().toLocaleString();
                    }else if(element.Type == 6){
                        DefaultValue=response.data.collaborators;
                    }else if(element.Type == 2){
                        DefaultValue=element.data; 
                    } 
                    console.log("Default values"+JSON.stringify(DefaultValue));
                    jsonForm[item] = element.DefaultValue;
                   this.storyFormData.push(
                       {'lable':element.Title,'model':element.Field_Name,'value':element.DefaultValue,'required':element.Required,'readOnly':element.ReadOnly,'type':element.Type,'values':DefaultValue}
                       )
                  });
                this.form = jsonForm;
                  console.log(JSON.stringify(this.form ));
            }else{
            console.log("fail---");
            }
        });
    }
saveStory(){
    console.log("post____data"+JSON.stringify(this.form));
    this._service.saveStory(this.form,(response)=>{
    });
}
}