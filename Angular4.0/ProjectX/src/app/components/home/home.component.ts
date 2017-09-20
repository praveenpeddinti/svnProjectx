import { LoginService } from '../../services/login.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { NgForm } from '@angular/forms';
import { Component, OnInit,ViewChild,Input,NgZone } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http, Response } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import {SharedService} from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
// import {ActivityService} from '../../services/activity.service';
declare var jQuery:any;


@Component({
    selector: 'home-view',
    providers: [LoginService,AuthGuard],
    templateUrl: 'home-component.html',
    styleUrls: ['./home-component.css'],    
    	
})

export class HomeComponent{
    public selectedProject:any;
   // public projects:any=[{'label':'ProjectX',value:{'id':1,'name':'ProjectX.0'}},{'label':'Techo2',value:{'id':2,'name':'Techo2'}}];
    //public optionTodisplay:any=[{'type':"",'filterValue':this.projects}];
    public users=JSON.parse(localStorage.getItem('user'));
    // public Email=localStorage.getItem('Email');
    // public profilePicture=localStorage.getItem('profilePicture');
    // public entryForm={};
    // public verified =0;
    // public submitted=false;
    // private cities=[];
    // private page=0;
    // public dashboardScroll=true;
    // public homeFlag=true;
    // public projectFlag;
    // public limit=10;
    // public saved;
    // public projectDetails=[];
    // public assignedToDataCount;
    // public followersDetailsCount;
    // public projectwiseInfo=[];
    // public totalProjectCount;
    // public flag:any;
    // public activityDetails=[];
    // public activityPage=0;
    // public projects=[];
    // private optionTodisplay=[];
    // public activityDropdownFlag=0;
    // public activitydropdownDetails=[];
    // public ProjectId;

    private projName;
    private checkOutUrl="";
    private svnServer = "http://10.10.73.16/svn/"
    //public offset=0;
    public rows = [];
      ngOnInit() {
          this.route.queryParams.subscribe(
        params => 
            { 
                // localStorage.
                this.projName=params['ProjectName'];
                this.navigateToFolder(this.projName);
        })
         
   }

   ngAfterViewInit() {
                jQuery('#createProjectDiv').show();
                jQuery('#showLogDiv').hide();
                jQuery('#createUserDiv').hide();
    }
    // ngDoCheck() {
    //     localStorage.setItem("currentDirPos",this.fileNavigator.join("/"));
    //     alert("DoCheck===>"+this.fileNavigator.join("/"));
    // }
    // ngOnDestroy() {
    //     localStorage.setItem("currentDirPos","");
    //     alert("destroy called....");

    //     }
     constructor(
          private _router: Router,
          private route: ActivatedRoute,
         private _service: LoginService,
          private _authGuard:AuthGuard,
          private shared:SharedService,
          private _ajaxService: AjaxService,
          private zone:NgZone,
        //   private _activityService: ActivityService
          ) { }


    private repo=[];
    private fileNavigator = [];

    createProject(){
    var sendData={
           userId:JSON.parse(this.users.Id),
           repName:this.projName
           }
           
        //    alert(JSON.stringify(sendData));
         this._ajaxService.AjaxSubscribe('site/create-repository',sendData,(result)=>
        {  
        //    alert("----repodata----"+JSON.stringify(result));
           this.fileNavigator.push(this.projName);
           this.repo =this.prepareFileStructure(result.data);
           })
    }

    navigateToFolder(dirName){
        
        var sendData={
            directory :(this.fileNavigator.length>0)?this.fileNavigator.join("/")+"/"+dirName : dirName
        };
        // alert("navigateToFolder===>"+JSON.stringify(sendData));
        this._ajaxService.AjaxSubscribe('site/get-repository-structure',sendData,(result)=>
        {  jQuery('#createProjectDiv').show();
           jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').hide();
        //    alert("----repodata----"+JSON.stringify(result));
        //    if(action != "pop"){
           this.fileNavigator.push(dirName);
           this.checkOutUrl = this.svnServer + this.fileNavigator.join("/");
        //    }else{
        //       var idx =  this.fileNavigator.indexOf(dirName);
        //       this.fileNavigator.splice(idx,1); 
        //    }
           this.repo =this.prepareFileStructure(result.data);
           })
    }

    comeBackToFolder(dirName){
        //  this.navigateToFolder(dirName,"pop")
        var idx =  this.fileNavigator.indexOf(dirName);
        var len = this.fileNavigator.length;
        var rem = len - idx;
        // alert("comeBack+++++"+JSON.stringify(this.fileNavigator));
        // alert("removingLen=>"+rem);
        // alert("idx==>"+idx);
        var idx =  this.fileNavigator.indexOf(dirName);
        this.fileNavigator.splice(idx,rem);
        this.navigateToFolder(dirName);
    }

    prepareFileStructure(data){
        var fileStructure = [];
        for(let datum in data){
            fileStructure.push(data[datum]);
        }
        return fileStructure;
    }

    createFolder(fodlerName){
        alert(fodlerName);
        var sendData={
            curerntDirectory :this.fileNavigator.join("/"),
            newFolder:fodlerName
        };
        this._ajaxService.AjaxSubscribe('site/create-folder',sendData,(result)=>
        {  jQuery('#createProjectDiv').show();
           jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').hide();
           alert("----repodata----"+JSON.stringify(result));
        //    if(action != "pop"){
        //    this.fileNavigator.push(dirName);
        //    }else{
        //       var idx =  this.fileNavigator.indexOf(dirName);
        //       this.fileNavigator.splice(idx,1); 
        //    }
           this.repo =this.prepareFileStructure(result.data);
           })
    }
    
    createUser(){
    var sendData={
           userId:JSON.parse(this.users.Id),
           userName:'moin',
           password:'minimum8'
           }
           
         this._ajaxService.AjaxSubscribe('site/create-user',sendData,(result)=>
        {
           jQuery('#createProjectDiv').hide();
           jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').show();
           console.log("----repodata----"+JSON.stringify(result));
           this.repo=result.data;
           })
    }
    
    showLog(){
    var sendData={
           userId:JSON.parse(this.users.Id),
           repName:'ProjectX'
           }
           
         this._ajaxService.AjaxSubscribe('site/show-svnlog',sendData,(result)=>
        {
           jQuery('#createProjectDiv').hide();
           jQuery('#createUserDiv').hide();
           jQuery('#showLogDiv').show();
           
           console.log("----repodata----"+JSON.stringify(result));
           this.repo=result.data;
           })
    }
}
