import {Component} from '@angular/core';
import {NavController, NavParams,Platform } from 'ionic-angular';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
import { Camera } from 'ionic-native';

@Component({
    selector: 'page-story-create',
    templateUrl: 'story-create.html'
})
export class StoryCreatePage {
    base64Image
    file: File;
    public showEditableFieldOnly = [];
    private submitted: boolean = false;
    constructor(public navCtrl: NavController,
        public navParams: NavParams,
        private globalService: Globalservice,
        private constants: Constants) {

        this.globalService.newStoryTemplate();
    }
     
    onStoryCreate(form): void { 
      console.log("create task submit button"); 
       var params = {
            "title": "Title Mobile",
            "description": "<p>Testing Creation</p>\n",
            "tasks": ["UI",
                "PeerReview",
                "QA"],
            "planlevel": "1",
            "priority": "2"
        }
        this.globalService.createStoryORTask(this.constants.createStory, params);
 }
 
// onChange(event) {
//    let file = event.srcElement.files;
//    let postData = {field1:"field1", field2:"field2"}; // Put your form data variable. This is only example.
////    Constants.postWithFile(this.baseUrl + "add-update",postData,file).then(result => {
////        console.log(result);
////    });
//}
 onChange(event: EventTarget) {
        let eventObj: MSInputMethodContext = <MSInputMethodContext> event;
        let target: HTMLInputElement = <HTMLInputElement> eventObj.target;
        let files: FileList = target.files;
        this.file = files[0];
        console.log(this.file);
    }
    accessGallery(){
   Camera.getPicture({
     sourceType: Camera.PictureSourceType.SAVEDPHOTOALBUM,
     destinationType: Camera.DestinationType.DATA_URL
    }).then((imageData) => {
      this.base64Image = 'data:image/jpeg;base64,'+imageData;
     }, (err) => {
      console.log(err);
    });
  }
  }
