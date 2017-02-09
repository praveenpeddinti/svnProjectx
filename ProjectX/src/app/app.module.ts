import { NgModule,CUSTOM_ELEMENTS_SCHEMA }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { RouterModule }   from '@angular/router';
import { HttpModule }    from '@angular/http';
import { AppComponent }  from './app.component';
import { LoginComponent }  from './components/login/login.component';
import { HomeComponent }  from './components/home/home.component';
import { StoryDashboardComponent }  from './components/story-dashboard/story-dashboard.component';
import { Ng2DropdownModule } from 'ng2-material-dropdown';
import { DatePickerModule } from 'ng2-datepicker';
//import { Typeahead } from 'ng2-typeahead';
import { MentionModule } from 'angular2-mentions/mention';
import {StoryService} from './services/story.service';
import { CKEditorModule } from 'ng2-ckeditor';
import {Ng2DragDropModule} from "ng2-drag-drop";
// HashLocationStrategy added to avoid Refresh Problems on Web Server....
import {LocationStrategy, HashLocationStrategy} from '@angular/common';
import {LoginService, Collaborator} from './services/login.service';
import {AjaxService} from './ajax/ajax.service';
import {FlexLayoutModule} from '@angular/flex-layout';
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';
import {AuthGuard} from './services/auth-guard.service';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { StoryComponent }  from './components/story/story-form.component';
import { StoryDetailComponent }  from './components/story-detail/story-detail.component';
import { StoryEditComponent } from './components/story-edit/story-edit.component';
const ROUTES=[
              {path: '',redirectTo: 'login',pathMatch: 'full' },
              {path: 'home',children:[
                { path: '' , component: HomeComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
              {path: 'login', component: LoginComponent},
               {path: 'story-dashboard',children:[
                { path: '' , component: StoryDashboardComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
             
              {path: 'story-detail', component: StoryDetailComponent},
              {path: 'story-edit', component: StoryEditComponent},
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
   NgxDatatableModule,
   RouterModule.forRoot(ROUTES)
  ],

  declarations: [ AppComponent,LoginComponent,HomeComponent, HeaderComponent,FooterComponent,StoryComponent,StoryDashboardComponent,StoryDetailComponent, StoryEditComponent, ],
  bootstrap:    [ AppComponent ],
  providers:[LoginService,AjaxService,AuthGuard,{provide: LocationStrategy, useClass: HashLocationStrategy},StoryService],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA],
})
export class AppModule {
   public onPageChange(event) {
   //  alert("on change");
            //this.loadFromServer(event.activePage, event.rowsOnPage);
    }
 }
 
