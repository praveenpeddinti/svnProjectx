import { Component} from '@angular/core';
import { LoginService } from '../../services/login.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { GlobalVariable } from '../../config';
import { Router,ActivatedRoute } from '@angular/router';

@Component({
    selector: 'home-view',
    providers: [LoginService,AuthGuard],
    templateUrl: 'home-component.html'       
    	
})

export class HomeComponent{
    public selectedProject:any;
    public projects:any=[{'label':'ProjectX',value:{'id':1,'name':'ProjectX.0'}},{'label':'Techo2',value:{'id':2,'name':'Techo2'}}];
    public optionTodisplay:any=[{'type':"",'filterValue':this.projects}];
    ngOnInit() {
        localStorage.setItem('ProjectName','');
        localStorage.setItem('ProjectId','');
   }
     constructor(
          private _router: Router,
         private _service: LoginService,
          private _authGuard:AuthGuard
          ) { }
    
changeProject(){
console.log("s__P"+JSON.stringify(this.selectedProject));
localStorage.setItem('ProjectName',this.selectedProject.name);
localStorage.setItem('ProjectId',this.selectedProject.id);
 this._router.navigate(['project',this.selectedProject.name,'list']);
}
}