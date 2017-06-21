import {AutoCompleteItem, AutoCompleteItemComponent} from 'ionic2-auto-complete';
 
@AutoCompleteItem({
  template : `<img src="build/images/flags/{{data.name}}.png" class="flag" /> <span [innerHTML]="data.name | boldbegin:keyword"></span>`,
  //pipes    : [AUTOCOMPLETE_PIPES]
})
export class AutoCompTestItem extends AutoCompleteItemComponent{
    constructor() { 
        super(); 
        console.log("the auto complete test view");
    }
}