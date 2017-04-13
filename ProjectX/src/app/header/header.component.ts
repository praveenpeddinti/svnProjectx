import { Component, OnInit,Input} from '@angular/core';
import { Router} from '@angular/router';
import { Headers, Http } from '@angular/http';
import { LoginService, Collaborator } from '../services/login.service';
import { AjaxService } from '../ajax/ajax.service';
declare var jQuery:any;
@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css'],
  providers: [LoginService]
})

export class HeaderComponent implements OnInit {
  public users=JSON.parse(localStorage.getItem('user'));
  public searchresults;
   constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http,
    private _service: LoginService
       ) { }

  ngOnInit() {
  }
  logout() { 
        this._service.logout((data)=>{ 
              this._router.navigate(['login']);  
        });

    }

  globalSearchNavigate(){
    var searchString=jQuery("#globalsearch").val().trim();
     //this.searchresults=searchString;
    //  var searchString=searchString.replace("#","");
         if(searchString=='' || searchString=='undefined'){ 
           this.showErrorFunction("searchError","Please Search.")
         }else{
           this._router.navigate(['search'],{queryParams: {SearchString:searchString}});
         }
      
    }
  showErrorFunction(id,message){
          jQuery("#"+id).html(message);
          jQuery("#"+id).show();
          jQuery("#"+id).fadeOut(4000);
  }
}
