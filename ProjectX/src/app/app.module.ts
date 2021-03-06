import { NgModule,CUSTOM_ELEMENTS_SCHEMA }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { RouterModule }   from '@angular/router';
import { HttpModule }    from '@angular/http';
import { AppComponent }  from './app.component';
import { LoginComponent }  from './components/login/login.component';
import { HomeComponent }  from './components/home/home.component';
import { StoryDashboardComponent }  from './components/story-dashboard/story-dashboard.component';
//import { Ng2DropdownModule } from 'ng2-material-dropdown';
//import { DatePickerModule } from 'ng2-datepicker';
//import { Typeahead } from 'ng2-typeahead';
//import { MentionModule } from 'angular2-mentions/mention';
import {StoryService} from './services/story.service';
import { CKEditorModule } from 'ng2-ckeditor';
//import {Ng2DragDropModule} from "ng2-drag-drop";
import {DropdownModule,CalendarModule,AutoCompleteModule,CheckboxModule,BreadcrumbModule,MenuItem} from 'primeng/primeng'; 
// HashLocationStrategy added to avoid Refresh Problems on Web Server....
import {LocationStrategy, HashLocationStrategy} from '@angular/common';
import {LoginService, Collaborator} from './services/login.service';
import {AjaxService} from './ajax/ajax.service';
//import {FlexLayoutModule} from '@angular/flex-layout';
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';
import {AuthGuard} from './services/auth-guard.service';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { StoryComponent }  from './components/story/story-form.component';
import { StoryDetailComponent }  from './components/story-detail/story-detail.component';
import { StoryEditComponent } from './components/story-edit/story-edit.component';
import {TinyMCE} from './tinymce.component';
import {FileDropModule} from 'angular2-file-drop';
import { FileUploadService } from './services/file-upload.service';
//import {Ng2AutoCompleteModule} from 'ng2-auto-complete';
import {MentionService} from './services/mention.service';
import {SummerNoteEditorService} from './services/summernote-editor.service';
//import { GlobalPipe } from './shared/global.pipe';
import { TruncatePipe } from 'angular2-truncate';
import { SearchComponent }  from './components/search/search.component';
import { TimeReportComponent }  from './components/time-report/time-report.component';
import {TimeReportService} from './services/time-report.service';
import { NotificationComponent }  from './components/notification/notification.component';
import { BreadcrumbComponent } from './components/breadcrumb/breadcrumb.component';
//import {Ng2BreadcrumbModule} from 'ng2-breadcrumb/ng2-breadcrumb'; /*** Important Module */
import {SharedService} from './services/shared.service';

import { StandupComponent }  from './components/standup/standup.component';
import {PageNotFoundComponent} from './components/pagenotfound/pagenotfound.component';
import {UrlSerializer} from '@angular/router';
import {CustomUrlSerializer} from './CustomUrlSerializer';
import { CookieService } from 'angular2-cookie/services/cookies.service';
const ROUTES=[
              {path: '',redirectTo: 'login',pathMatch: 'full' },
              {path: '404',component: PageNotFoundComponent },
              {path: 'home',children:[
                { path: '' , component: HomeComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
              {path: 'login', component: LoginComponent},
                {path: 'project/:projectName/list',children:[
                { path: '' , component: StoryDashboardComponent,data:{breadcrumb:'Dashboard'}},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
                {path: 'project/:projectName/new',children:[
                { path: '' , component: StoryComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
             {path: 'project/:projectName/:id/details',children:[
                { path: '' , component: StoryDetailComponent,data:{breadcrumb:'Detail'}},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'project/:projectName/:id/edit',children:[
                { path: '' , component: StoryEditComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'project/:projectName/search',children:[
                { path: '' , component: SearchComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'search',children:[
                { path: '' , component: SearchComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'project/:projectName/time-report',children:[
                { path: '' , component: TimeReportComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
              {path: 'collaborator/notifications',children:[
                { path: '' , component: NotificationComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'standup',children:[
                { path: '' , component: StandupComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'project/:projectName/:id/error',children:[
                { path: '' , component: PageNotFoundComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'pagenotfound',children:[
                { path: '' , component: PageNotFoundComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
                {path: 'project/:projectName/error',children:[
                { path: '' , component: PageNotFoundComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'error',children:[
                { path: '' , component: PageNotFoundComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               { path: '**', component: PageNotFoundComponent }
             ];
@NgModule({
  imports:      [
   BrowserModule,
   FormsModule,
   ReactiveFormsModule ,
   HttpModule,

 //  Ng2DropdownModule,
 //  DatePickerModule,
 //  MentionModule,

   CKEditorModule,
   NgxDatatableModule,
   FileDropModule,
   DropdownModule,
   CheckboxModule,
   CalendarModule,
   AutoCompleteModule,
   RouterModule.forRoot(ROUTES),
   //Ng2BreadcrumbModule.forRoot()
  ],

  declarations: [ AppComponent,LoginComponent,HomeComponent, HeaderComponent,FooterComponent,StoryComponent,StoryDashboardComponent,StoryDetailComponent, StoryEditComponent,TruncatePipe,SearchComponent,NotificationComponent,StandupComponent,TimeReportComponent,PageNotFoundComponent,BreadcrumbComponent ],
  bootstrap:    [ AppComponent ],
  providers:[FileUploadService, LoginService,AjaxService,AuthGuard,StoryService,MentionService,SummerNoteEditorService,TimeReportService,SharedService,CookieService,{provide:UrlSerializer,useClass:CustomUrlSerializer}
  ],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA],
})
export class AppModule {
   public onPageChange(event) {
   //  alert("on change");
            //this.loadFromServer(event.activePage, event.rowsOnPage);
    }
 }
 
