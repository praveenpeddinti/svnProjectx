import { register } from 'ts-node/dist';
import { Component, Directive } from '@angular/core';
import { StoryService } from '../../services/story.service';
import { Router } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';

@Component({
    selector: 'story-dashboard-view',
    providers: [StoryService],
    templateUrl: 'story-dashboard-component.html',
})

export class StoryDashboardComponent {
    rows = [];
    count: number = 0;
    offset: number = 0;
    limit: number = 10;
    sortvalue = 'TicketId';
    headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });

    constructor(
        private _router: Router,
        private _service: StoryService, private http: Http) { console.log("in constructor"); }
    
    ngOnInit() {
        this.page(this.offset, this.limit,this.sortvalue);
    }

    page(offset, limit,sortvalue) {
        this._service.getAllStoryDetails(1, offset, limit,sortvalue, (response) => {
            let jsonForm = {};
            if (response.statusCode == 200) {
                const start = offset * limit;
                const end = start + limit;
                let rows = [...this.rows];
                for (let i = 0; i < 10; i++) {
                    rows[i + start] = response.data[i];
                }
                this.rows = rows;
                this.count = response.totalCount;
                console.log(response.data.length + "responseoooo fetch" + this.rows);
            } else {
                console.log("fail---");
            }
        });
    }

    onPage(event) {
        console.log('Page Event', event);
        this.page(event.offset, event.limit,'TicketId');
    }
 /*onSort(event) {

    console.log(event.sorts[0].dir,' Sort Event', event.sorts[0].prop);
 }*/
 onActivate(event) {
    this._router.navigate(['story-detail', event.row.TicketId]);
  }
    renderStoryForm() {
        this._router.navigate(['story-form']);
    }
    /** @Praveen P
    * Pass the TicketId for story-detail component
    */
    showStoryDetail(row) {
        //console.log('Toggled Expand Row!', row.TicketId);
        this._router.navigate(['story-detail', row.TicketId]);
    }

}

