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
    private projId;
    private checkOutUrl="";
    private team = [];
    private svnServer = "http://10.10.73.16/svn/"
    private svnLogs = [];
    //public offset=0;
    public rows = [];
      ngOnInit() {
          this.route.queryParams.subscribe(
        params => 
            { 
                // alert(JSON.stringify(this.users));
                // localStorage.
                this.projName=params['ProjectName'];
                this.projId=params['ProjectId'];
                var sendData = {
                    ProjectName:this.projName,
                    ProjectId:this.projId
                };
                this._ajaxService.AjaxSubscribe('site/get-project-team',sendData,(result)=>
                {  
                        // alert(JSON.stringify(result));
                        this.team = this.prepareTeamPermissionsData(result.data);
                });
                this.navigateToFolder(this.projName);
                this.showLog();
        })
         
   }

   ngAfterViewInit() {
                jQuery('#createProjectDiv').show();
                // jQuery('#showLogDiv').hide();
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
           jQuery('#createProjectDiv').show();
        //    jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').hide();
        //    alert(JSON.stringify(sendData));
        //  this._ajaxService.AjaxSubscribe('site/create-repository',sendData,(result)=>
        // {  
        // //    alert("----repodata----"+JSON.stringify(result));
        //    this.fileNavigator.push(this.projName);
        //    this.repo =this.prepareFileStructure(result.data);
        //    })
    }

    navigateToFolder(dirName){
        
        var sendData={
            directory :(this.fileNavigator.length>0)?this.fileNavigator.join("/")+"/"+dirName : dirName,
            userName:this.users.username,
           password:'minimum8'
        };
        // alert("navigateToFolder===>"+JSON.stringify(sendData));
        this._ajaxService.AjaxSubscribe('site/get-repository-structure',sendData,(result)=>
        {  jQuery('#createProjectDiv').show();
        //    jQuery('#showLogDiv').hide();
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
            newFolder:fodlerName,
            userName:this.users.username,
            password:'minimum8'
        };
        this._ajaxService.AjaxSubscribe('site/create-folder',sendData,(result)=>
        {  jQuery('#createProjectDiv').show();
        //    jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').hide();
        //    alert("----repodata----"+JSON.stringify(result));
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
    // var sendData={
    //        userId:JSON.parse(this.users.Id),
    //        userName:'moin',
    //        password:'minimum8'
    //        }
           jQuery('#createProjectDiv').hide();
        //    jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').show();
        //  this._ajaxService.AjaxSubscribe('site/create-user',sendData,(result)=>
        // {
           
        //    console.log("----repodata----"+JSON.stringify(result));
        //    this.repo=result.data;
        //    })
    }
    
    showLog(){
    var sendData={
           userId:this.users.Id,
           repName:this.projName,
           userName:this.users.username,
           password:'minimum8',
           role:'RW'
           };
           
         this._ajaxService.AjaxSubscribe('site/show-svnlog',sendData,(result)=>
        {
           jQuery('#createProjectDiv').hide();
           jQuery('#createUserDiv').hide();
        //    jQuery('#showLogDiv').show();
           
        //    alert("----repodata----"+JSON.stringify(result));
           this.svnLogs = result.data;
        //    for(let elem of this.svnLogs){
        //       var d = new Date(Date.parse(elem.date));
        //       var locale = "en-us";
        //         var month = d.toLocaleString(locale, { month: "short" });
        //            alert(d.toLocaleDateString());
        //            alert(month);
               
        //    }

        //    this.repo=result.data;
           })
    }

    prepareTeamPermissionsData(teamData=[]){
        var teamPermissions = [];
        for(let teamMember of teamData){
            teamPermissions.push({id:teamMember.Id,userName: teamMember.Name,ProfilePic:teamMember.ProfilePic, read:false, write:false, modified:false});
        }
        return teamPermissions;
    }

    savePermissions(){
        var permissonsToBeUpdated = this.getModifiedList(this.team);
        var sendData = {
            projectName:this.projName,
            projectId:this.projId,
            userData:permissonsToBeUpdated
        };
        // alert("----permissions----"+JSON.stringify(sendData));
        this._ajaxService.AjaxSubscribe('site/create-user',sendData,(result)=>
        {
           jQuery('#createProjectDiv').hide();
        //    jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').show();
           console.log("----repodata----"+JSON.stringify(result));
           alert(result.data);
        //    this.repo=result.data;
           })

    }

    getModifiedList(teamList=[]){
        var modifiedPermissions = [];
        for(let teamMember of teamList){
            if(teamMember.modified){
                var data={userId:teamMember.id,userName:teamMember.userName,password:"minimum8", role:"R"};
                if((teamMember.read && teamMember.write) || teamMember.write){
                   data.role="RW";
                }else if(teamMember.read == false && teamMember.write == false){
                   data.role="REM"; 
                }
                modifiedPermissions.push(data);
            }
        }
        return modifiedPermissions;
    }
}
