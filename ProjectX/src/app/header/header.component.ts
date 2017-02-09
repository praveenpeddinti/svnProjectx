import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { LoginService, Collaborator } from '../services/login.service';
import { AjaxService } from '../ajax/ajax.service';
@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css'],
  providers: [LoginService]
})
export class HeaderComponent implements OnInit {
  public users=JSON.parse(localStorage.getItem('user'));
   constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http,
    private _service: LoginService
   ) { }

  ngOnInit() {
  }
  logout() {
        this._service.logout();
    }

}
