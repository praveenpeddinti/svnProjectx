

import { LoginComponent }  from './components/login/login.component';
import { HomeComponent }  from './components/home/home.component';
import { StoryDashboardComponent }  from './components/story-dashboard/story-dashboard.component';
// HashLocationStrategy added to avoid Refresh Problems on Web Server....
import {LoginService, Collaborator} from './services/login.service';
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';
import {AuthGuard} from './services/auth-guard.service';
import { StoryComponent }  from './components/story/story-form.component';
import { StoryDetailComponent }  from './components/story-detail/story-detail.component';
import { StoryEditComponent } from './components/story-edit/story-edit.component';
import { SearchComponent }  from './components/search/search.component';
import { TimeReportComponent }  from './components/time-report/time-report.component';
import {TimeReportService} from './services/time-report.service';
import { NotificationComponent }  from './components/notification/notification.component';
import { BreadcrumbComponent } from './components/breadcrumb/breadcrumb.component';
import { StandupComponent }  from './components/standup/standup.component';
import {PageNotFoundComponent} from './components/pagenotfound/pagenotfound.component';




export const ROUTES=[
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