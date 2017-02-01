import { NgModule,CUSTOM_ELEMENTS_SCHEMA }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { RouterModule }   from '@angular/router';
import { HttpModule }    from '@angular/http';
import { AppComponent }  from './app.component';
import { LoginComponent }  from './components/login/login.component';
import { HomeComponent }  from './components/home/home.component';
import { Ng2DropdownModule } from 'ng2-material-dropdown';
// HashLocationStrategy added to avoid Refresh Problems on Web Server....
import {LocationStrategy, HashLocationStrategy} from '@angular/common';
import {LoginService, User} from './services/login.service';
import {AjaxService} from './ajax/ajax.service';
import {FlexLayoutModule} from '@angular/flex-layout';
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';
import {AuthGuard} from './services/auth-guard.service';

const ROUTES=[
              {path: '',redirectTo: 'login',pathMatch: 'full' },
              {path: 'home',children:[
                { path: '' , component: HomeComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
              {path: 'login', component: LoginComponent},
             ];
@NgModule({
  imports:      [ FlexLayoutModule,BrowserModule ,FormsModule,ReactiveFormsModule ,HttpModule,Ng2DropdownModule,
  RouterModule.forRoot(ROUTES)
  ],
  declarations: [ AppComponent,LoginComponent,HomeComponent, HeaderComponent,FooterComponent ],
  bootstrap:    [ AppComponent ],
  providers:[LoginService,AjaxService,AuthGuard,{provide: LocationStrategy, useClass: HashLocationStrategy}],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA],
})
export class AppModule { }
