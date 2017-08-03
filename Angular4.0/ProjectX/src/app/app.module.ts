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
import { BucketComponent }  from './components/bucket/bucket.component';
import {BucketService} from './services/bucket.service';
import { ActivitiesComponent } from './components/activities/activities.component';
import {RoundProgressModule} from 'angular-svg-round-progressbar';
import { ChildtaskComponent } from './components/childtask/childtask.component';
import { ProjectDetailComponent } from './components/project-detail/project-detail.component';
//import {Ng2DropdownModule} from 'ng2-material-dropdown';
import {ToasterModule, ToasterService} from 'angular2-toaster';
import { SpinnerComponentModule } from 'ng2-component-spinner';
import { CreateUserComponent } from './components/create-user/create-user.component';
import { ProjectDashboardComponent } from './components/project-dashboard/project-dashboard.component';
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
               {path: 'project/:projectName/bucket',children:[
                { path: '' , component: BucketComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'project/:projectName/create-user',children:[
                { path: '' , component: CreateUserComponent},
                { path: '' , component: HeaderComponent,outlet:'header'},
                { path: '' , component: FooterComponent,outlet:'footer'}
               ],canActivate:[AuthGuard]},
               {path: 'project/:projectName/project-dashboard',children:[
                { path: '' , component: ProjectDashboardComponent},
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
   NgxDatatableModule,
   FileDropModule,
   DropdownModule,
   CheckboxModule,
   CalendarModule,
   AutoCompleteModule,
   BrowserAnimationsModule,
   RouterModule.forRoot(ROUTES),
   RoundProgressModule,
   ToasterModule,
   SpinnerComponentModule,
   //Ng2DropdownModule
  ],


  declarations: [ AppComponent,LoginComponent,HomeComponent, HeaderComponent,FooterComponent,StoryComponent,StoryDashboardComponent,StoryDetailComponent, StoryEditComponent,TruncatePipe,SearchComponent,NotificationComponent,StandupComponent,TimeReportComponent,PageNotFoundComponent,BreadcrumbComponent,BucketComponent, ChildtaskComponent,ActivitiesComponent, ProjectDetailComponent, CreateUserComponent, ProjectDashboardComponent ],
  bootstrap:    [ AppComponent ],
  providers:[FileUploadService, LoginService,AjaxService,AuthGuard,StoryService,MentionService,SummerNoteEditorService,TimeReportService,SharedService,CookieService,BucketService,{provide:UrlSerializer,useClass:CustomUrlSerializer}
  ],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA],
})
export class AppModule {
 
 }
 
