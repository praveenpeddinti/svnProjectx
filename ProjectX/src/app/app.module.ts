import { NgModule,CUSTOM_ELEMENTS_SCHEMA }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { RouterModule }   from '@angular/router';
import { HttpModule }    from '@angular/http';
import { AppComponent }  from './app.component';
import { LoginComponent }  from './components/login/login.component';
import { HomeComponent }  from './components/home/home.component';
import { Ng2DropdownModule } from 'ng2-material-dropdown';
import {LoginService, User} from './services/login.service'
const ROUTES=[
              {path: '',redirectTo: '/login',pathMatch: 'full' },
              {path: 'login',component: LoginComponent},
              {path: 'home', component: HomeComponent },
             ];
@NgModule({
  imports:      [ BrowserModule ,FormsModule,ReactiveFormsModule ,HttpModule,Ng2DropdownModule,
  RouterModule.forRoot(ROUTES)
  ],
  declarations: [ AppComponent,LoginComponent,HomeComponent ],
  bootstrap:    [ AppComponent ],
  providers:[LoginService],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA],
})
export class AppModule { }
