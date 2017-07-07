import { NgModule,CUSTOM_ELEMENTS_SCHEMA }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { RouterModule }   from '@angular/router';
import { HttpModule }    from '@angular/http';
import { AppComponent }  from './app.component';
import { LoginComponent }  from './components/login/login.component';
import { HomeComponent }  from './components/home/home.component';
import { StoryDashboardComponent }  from './components/story-dashboard/story-dashboard.component';
import {StoryService} from './services/story.service';
import {DropdownModule,CalendarModule,AutoCompleteModule,CheckboxModule,BreadcrumbModule,MenuItem} from 'primeng/primeng'; 
// HashLocationStrategy added to avoid Refresh Problems on Web Server....
import {LocationStrategy, HashLocationStrategy} from '@angular/common';
import {LoginService, Collaborator} from './services/login.service';
import {AjaxService} from './ajax/ajax.service';
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';
import {AuthGuard} from './services/auth-guard.service';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { StoryComponent }  from './components/story/story-form.component';
import { StoryDetailComponent }  from './components/story-detail/story-detail.component';
import { StoryEditComponent } from './components/story-edit/story-edit.component';
import {FileDropModule} from 'angular2-file-drop';
import { FileUploadService } from './services/file-upload.service';
import {MentionService} from './services/mention.service';
import {SummerNoteEditorService} from './services/summernote-editor.service';
import { TruncatePipe } from 'angular2-truncate';
import { SearchComponent }  from './components/search/search.component';
import { TimeReportComponent }  from './components/time-report/time-report.component';
import {TimeReportService} from './services/time-report.service';
import { NotificationComponent }  from './components/notification/notification.component';
import { BreadcrumbComponent } from './components/breadcrumb/breadcrumb.component';
import {SharedService} from './services/shared.service';
import { StandupComponent }  from './components/standup/standup.component';
import {PageNotFoundComponent} from './components/pagenotfound/pagenotfound.component';
import {UrlSerializer} from '@angular/router';
import {CustomUrlSerializer} from './CustomUrlSerializer';
import { CookieService } from 'angular2-cookie/services/cookies.service';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';

import {ROUTES} from './app.router';

@NgModule({
  imports:      [
   BrowserModule,
   FormsModule,
   ReactiveFormsModule ,
   HttpModule,
   NgxDatatableModule,
   FileDropModule,
   DropdownModule,
   CheckboxModule,
   CalendarModule,
   AutoCompleteModule,
   BrowserAnimationsModule,
   RouterModule.forRoot(ROUTES),
  ],

  declarations: [ AppComponent,LoginComponent,HomeComponent, HeaderComponent,FooterComponent,StoryComponent,StoryDashboardComponent,StoryDetailComponent, StoryEditComponent,TruncatePipe,SearchComponent,NotificationComponent,StandupComponent,TimeReportComponent,PageNotFoundComponent,BreadcrumbComponent ],
  bootstrap:    [ AppComponent ],
  providers:[FileUploadService, LoginService,AjaxService,AuthGuard,StoryService,MentionService,SummerNoteEditorService,TimeReportService,SharedService,CookieService,{provide:UrlSerializer,useClass:CustomUrlSerializer}
  ],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA],
})
export class AppModule {
 
 }
 
