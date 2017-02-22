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
    sortvalue: string = "TicketId";
    sortorder: string = "desc";

    expanded: any = {};
    headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });

    constructor(
        private _router: Router,
        private _service: StoryService, private http: Http) { console.log("in constructor"); }

    ngOnInit() {
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
    }

    page(offset, limit, sortvalue, sortorder) {
        this._service.getAllStoryDetails(1, offset, limit, sortvalue, sortorder, (response) => {
            // alert(offset+"----Servicemethod---"+limit+"---sortvalue----"+sortvalue+"==="+sortorder);
            let jsonForm = {};
            if (response.statusCode == 200) {
                const start = offset * limit;
                const end = start + limit;
                let rows = [...this.rows];
                //alert(start + "--------" + end);
                // rows.splice(0);
                /*for (let i = 0; i < 10; i++) {
                    console.log("====server response===" + response.data[i]);
                    rows[i + start] = response.data[i];
                }*/
                
                for (let i = 0; i < 10; i++) {
                    rows[i+start] = response.data[i];
                    //console.log("==end======data table view===" + JSON.stringify(rows[i].TicketId));
                    
                }
                this.rows = rows;
                this.count = response.totalCount;
                //console.log("==end======data table view===" + JSON.stringify(this.rows));

            } else {
                console.log("fail---");
            }
        });
    }

    onPage(event) {//alert("----onpage====");
        //console.log('Page Event', event);
        this.offset = event.offset;
        this.limit = event.limit;
        //this.page(event.offset, event.limit,this.sortvalue);
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
    }
    onSort(event) {
        //alert(this.offset+"----OnSort---"+this.limit+"---sortvalue----"+this.sortvalue);
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        //alert(this.offset + "----OnSort---" + this.limit + "---sortvalue----" + this.sortvalue);
        //console.log(event, '----event----', event.sorts[0].dir, ' Sort Event', event.sorts[0].prop);
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
    }

    
  

    onActivate(event) {

if(event.hasOwnProperty("row")){
    console.log("yes, i have that property");
 this._router.navigate(['story-detail', event.row.TicketId]);
}
    
//
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

