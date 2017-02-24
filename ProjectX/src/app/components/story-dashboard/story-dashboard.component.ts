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
    loading: boolean = false;
    columns = [{ name: 'Id', flexGrow: 1, sortby: 'Id', class:'' }, { name: 'Title', flexGrow: 4, sortby: 'Title', class:'titlecolumn' }, { name: 'Assigned to', flexGrow: 2, sortby: 'assignedto', class:'' }, { name: 'Priority', flexGrow: 1, sortby: 'priority', class:'prioritycolumn' }, { name: 'Status', flexGrow: 1, sortby: 'workflow', class:'statusbold' }, { name: 'Bucket', flexGrow: 1, sortby: 'bucket', class:'' }, { name: 'Due Date', flexGrow: 1, sortby: 'duedate', class:'' }];
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
            } else {
                console.log("fail---");
            }
        });
    }

    onPage(event) {
        this.offset = event.offset;
        this.limit = event.limit;
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
    }


    onSort(event) {
        this.loading = true;
        // emulate a server request with a timeout
        setTimeout(() => {
            const rows = [...this.rows];
            const sort = event.sorts[0];
            rows.sort((a, b) => {
                for (var i = 0; i < a.length; i++) {
                    if (a[i].field_name == sort.prop) {
                        var fisrtValue = a[i].field_value;
                        var secondValue = b[i].field_value;
                        return fisrtValue.toString().localeCompare(secondValue) * (sort.dir === 'desc' ? -1 : 1);
                    }
                }
            });
            this.rows = rows;
            this.loading = false;
        }, 1000);
    }

    /** @Praveen P
        * Pass the TicketId for story-detail component
        */
    onActivate(event) {
        if (event.hasOwnProperty("row")) {
            this._router.navigate(['story-detail', event.row[0].field_value]);
        }
    }
    
    renderStoryForm() {
        this._router.navigate(['story-form']);
    }

}

