import {AutoCompleteItem, AutoCompleteItemComponent} from 'ionic2-auto-complete';

@AutoCompleteItem({
  template: `<img src="{{data.ProfilePic}}" class="user_flag" /> <span [innerHTML]="data.Name | boldprefix:keyword"></span>`
})
export class CustomAutocompleteItem extends AutoCompleteItemComponent{
    
}