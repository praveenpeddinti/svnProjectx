import {AutoCompleteItem, AutoCompleteItemComponent} from 'ionic2-auto-complete';

@AutoCompleteItem({
  template: `<div class="user"><img class="usericon" src="{{data.ProfilePic}}" class="user_flag" /> <span [innerHTML]="data.Name | boldprefix:keyword"></span></div>`
})
export class CustomAutocompleteItem extends AutoCompleteItemComponent{
    
}