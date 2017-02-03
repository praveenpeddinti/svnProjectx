import { NgModule,CUSTOM_ELEMENTS_SCHEMA }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { RouterModule }   from '@angular/router';
import { HttpModule }    from '@angular/http';
import { AppComponent }  from './app.component';
import { LoginComponent }  from './components/login/login.component';
import { HomeComponent }  from './components/home/home.component';
import { Ng2DropdownModule } from 'ng2-material-dropdown';
import { DatePickerModule } from 'ng2-datepicker';
import { Typeahead } from 'ng2-typeahead';
import { MentionModule } from 'angular2-mentions/mention';
import {StoryService} from './services/story.service';
import { CKEditorModule } from 'ng2-ckeditor';
import {Ng2DragDropModule} from "ng2-drag-drop";
// HashLocationStrategy added to avoid Refresh Problems on Web Server....
import {LocationStrategy, HashLocationStrategy} from '@angular/common';
import {LoginService, User} from './services/login.service';
import {AjaxService} from './ajax/ajax.service';
import {FlexLayoutModule} from '@angular/flex-layout';
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';
import {AuthGuard} from './services/auth-guard.service';
import { StoryComponent }  from './components/story/story-form.component';
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
  imports:      [
   BrowserModule,
   FormsModule,
   ReactiveFormsModule ,
   HttpModule,
   Ng2DropdownModule,
   DatePickerModule,
   MentionModule,
   CKEditorModule,
   Ng2DragDropModule,
   RouterModule.forRoot(ROUTES)
  ],

  declarations: [ AppComponent,LoginComponent,HomeComponent, HeaderComponent,FooterComponent,StoryComponent ],
  bootstrap:    [ AppComponent ],
  providers:[LoginService,AjaxService,AuthGuard,{provide: LocationStrategy, useClass: HashLocationStrategy},StoryService],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA],
})
export class AppModule { }
