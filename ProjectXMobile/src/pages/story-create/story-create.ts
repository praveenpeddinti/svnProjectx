import {Component} from '@angular/core';
import {NavController, NavParams} from 'ionic-angular';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';

@Component({
    selector: 'page-story-create',
    templateUrl: 'story-create.html'
})
export class StoryCreatePage {
    public showEditableFieldOnly = [];
    private submitted: boolean = false;
    constructor(public navCtrl: NavController,
        public navParams: NavParams,
        private globalService: Globalservice,
        private constants: Constants) {

        this.globalService.newStoryTemplate();
    }

    onStoryCreate(form): void {
        console.log("in onStroyCreate");
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
}
