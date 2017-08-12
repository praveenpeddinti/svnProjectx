import {Pipe, PipeTransform} from "@angular/core";
import { DatePipe } from '@angular/common';
import * as moment from 'moment';
@Pipe({
    name: 'dateFormat'
})
export class DateFormat implements PipeTransform {
    transform(value:string, args:string):any {
        if (value) {
        var momentDate = moment(value,moment.ISO_8601);
        if (!momentDate.isValid()) return value;
        return momentDate.format(args); 
        }
    }
}