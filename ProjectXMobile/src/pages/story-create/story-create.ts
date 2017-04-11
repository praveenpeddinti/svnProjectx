import {Component} from '@angular/core';
import {NavController, NavParams,Platform, LoadingController,PopoverController, ModalController } from 'ionic-angular';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
import { Camera } from 'ionic-native';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
import {CustomModalPage} from '../custom-modal/custom-modal';
import { User } from './user.interface';
declare var jQuery: any;

@Component({
    selector: 'page-story-create',
    templateUrl: 'story-create.html'
})
export class StoryCreatePage {
    public itemfield: Array<any>;
    public tasktypes: Array<any>;
   //    var params: {title?: any, description?: any, tasks?: any, planlevel?:any, priority?:any} = {};
     create: {title?: any, description?: any, tasks?: any, planlevel?:any, priority?:any} = {};
   // public configLists = ["UI", "Peer Review", "QA"];
    public userName: any = '';
    public user: any;
    base64Image
    file: File;
    public templatedataList: Array<{ id: string, title: string, assignData: string, readOnly: string, fieldType: string, fieldName: string}>;
    public tasktypeList: Array<{id:string, name:string, IsDefault:string, selected : boolean}>;
    public showEditableFieldOnly = [];
    public previousSelectIndex: any;
    public previousSelectedValue = "";
   // public displayFieldvalue = [];
    public readOnlyDropDownField: boolean = false;
    private submitted: boolean = false;
    constructor(
        public modalController: ModalController,
        public navParams: NavParams,
        private globalService: Globalservice,
        public popoverCtrl: PopoverController,
        public loadingController: LoadingController,
        private storage: Storage, private constants: Constants) {

            this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
           });

        globalService.newStoryTemplate(this.constants.templateForStoryCreation, this.navParams.get("id")).subscribe(
    (result) => {
            this.itemfield = result.data.story_fields;
            this.tasktypes = result.data.task_types;
            console.log("result newStoryTemplate "+ JSON.stringify(this.tasktypes));
            this.templatedataList = [];
            this.tasktypeList = [];

                     for (let i = 0; i < this.itemfield.length;  i++) {
                    var _id = this.itemfield[i].Id;
                    var _title = this.itemfield[i].Title;
                    var _assignData;
                    _assignData = this.itemfield[i].data;
                    var _readOnly = this.itemfield[i].readonly;
                    var _fieldType = this.itemfield[i].field_type;
                    var _fieldName = this.itemfield[i].Field_Name;
                    var _name = this.itemfield[i].data.Name;
                    this.templatedataList.push({
                        id: _id, title: _title, assignData: _assignData, readOnly: _readOnly, fieldType: _fieldType, fieldName: _fieldName});
                }

                for(let j = 0; j < this.tasktypes.length; j++){
                    var _id = this.tasktypes[j].Id;
                    var _Name = this.tasktypes[j].Name;
                    var _IsDefault = this.tasktypes[j].IsDefault;
                    var _selected = false;
                    if(_IsDefault == "1"){
                        _selected = true;
                    }
                    
                    this.tasktypeList.push({
                        id: _id, name: _Name, IsDefault: _IsDefault, selected: _selected});

                }
                
    }, (error) => {
                console.log("the error in ticker derais " + JSON.stringify(error));
            }
        );
    }

    ionViewDidLoad() {
    console.log('loaded at every Load the page');
   }

    onStoryCreate(form): void { 
        debugger
      console.log("create task submit button"); 
     if (form.valid) {
          // let loader = this.loadingController.create({ content: "Loading..."});  
         // loader.present();
          this.create.tasks = [];
          for (let taskTypeItem of this.tasktypeList) {
            if(taskTypeItem.selected == true){
                this.create.tasks.push(taskTypeItem.name);
            }
        }
       // this.create.description = "<p>Testing</p>\\n";
        this.create.planlevel = "2";
        this.create.priority =  "2";
           console.log("the create patams " + JSON.stringify(this.create));
        //    this.globalService.createStoryORTask(this.constants.createStory, this.create);
        this.globalService.createStoryORTask(this.constants.createStory, this.create).subscribe(
            (result)=>{
                console.log("the create tsk details are");
             //   loader.dismiss();
            },(error)=>{
              //  loader.dismiss();
                console.log("the story create error are---------> " + JSON.stringify(error));
            }
        );
     }
 }

     public selectCancel(index) {
        console.log("selectCancel --- " + index);
        this.showEditableFieldOnly[index] = false;
        setTimeout(() => {
            //document.getElementById("item_"+index).classList.remove("item-select");
        }, 300);
    }
    // checkbox(recipient) {
    //     recipient.selected = (recipient.selected) ? false : true;
    // }
 
 onChange(event: EventTarget) {
        let eventObj: MSInputMethodContext = <MSInputMethodContext> event;
        let target: HTMLInputElement = <HTMLInputElement> eventObj.target;
        let files: FileList = target.files;
        this.file = files[0];
        console.log(this.file);
    }
    openPopover(myEvent) {
        let userCredentials = { username: this.userName };
        let popover = this.popoverCtrl.create(PopoverPage, userCredentials);
        popover.present({
            ev: myEvent
        });
    }
openOptionsModal(fieldDetails, index){
        console.log("the model present");

        let optionsModal = this.modalController.create(CustomModalPage, {activeField: fieldDetails, activatedFieldIndex: index, displayList: fieldDetails.assignData });
                        optionsModal.onDidDismiss((data) => {
                            console.log("the dismiss data " + index + " ----- " + JSON.stringify(data));
                            if(data != null && (data.Name != data.previousValue)){
                                document.getElementById("field.title_field.id_" + index).innerHTML = data.Name;
                            }
                           // this.displayFieldvalue = [];
                        }); 
                    optionsModal.present();
    }



  }
