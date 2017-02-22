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
    /*oldonSort(event) {
        //alert(this.offset+"----OnSort---"+this.limit+"---sortvalue----"+this.sortvalue);
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        //alert(this.offset + "----OnSort---" + this.limit + "---sortvalue----" + this.sortvalue);
        //console.log(event, '----event----', event.sorts[0].dir, ' Sort Event', event.sorts[0].prop);
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
    }*/
loading: boolean = false;
    onSort(event) {
    // event was triggered, start sort sequence
    //console.log('Sort Event', event);
    this.loading = true;
    // emulate a server request with a timeout
    setTimeout(() => {
      const rows = [...this.rows];
      // this is only for demo purposes, normally
      // your server would return the result for
      // you and you would just set the rows prop
      const sort = event.sorts[0];
      //alert("sort----"+sort.prop);
      rows.sort((a, b) => {
         //alert("----a----"+a[sort.prop]);
        return a[sort.prop].localeCompare(b[sort.prop]) * (sort.dir === 'desc' ? -1 : 1);
      });

      this.rows = rows;
      //console.log("========sorting===" +JSON.stringify(this.rows));
      this.loading = false;
    }, 1000);
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

