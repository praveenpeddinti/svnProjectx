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
declare var bootbox:any;


@Component({
    selector: 'home-view',
    providers: [LoginService,AuthGuard],
    templateUrl: 'home-component.html',
    styleUrls: ['./home-component.css'],    
    	
})

export class HomeComponent{
    public selectedProject:any;

    public users=JSON.parse(localStorage.getItem('user'));
    private projName;
    private projId;
    private checkOutUrl="";
    private team = [];
    private svnServer = "http://10.10.73.16/svn/"
    private svnLogs = [];
    private tabToggler = 1;
    public rows = [];
    @ViewChild("folderName") folderNameValue:HTMLInputElement;
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
                // jQuery('#createProjectDiv').show();
                // jQuery('#showLogDiv').hide();
                // jQuery('#createUserDiv').hide();
    }

  
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

  

    navigateToFolder(dirName){
        
        var sendData={
            directory :(this.fileNavigator.length>0)?this.fileNavigator.join("/")+"/"+dirName : dirName,
            userId:this.users.Id,
            ProjectId:this.projId,
            userName:this.users.username,
           password:'minimum8'
        };
        // alert("navigateToFolder===>"+JSON.stringify(sendData));
        this._ajaxService.AjaxSubscribe('site/get-repository-structure',sendData,(result)=>
        {  

           this.fileNavigator.push(dirName.replace(/\s*/g,""));
           this.checkOutUrl = this.svnServer + this.fileNavigator.join("/");
           this.repo =this.prepareFileStructure(result.data);
           })
    }

    comeBackToFolder(dirName){
        //  this.navigateToFolder(dirName,"pop")
        var idx =  this.fileNavigator.indexOf(dirName);
        var len = this.fileNavigator.length;
        var rem = len - idx;
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

    createFolder(fodlerName:HTMLInputElement){
        // alert(fodlerName);
        var sendData={
            curerntDirectory :this.fileNavigator.join("/"),
            newFolder:fodlerName.value,
            userName:this.users.username,
            password:'minimum8'
        };
        var thisObj = this;
        if(fodlerName.value != ""){
            bootbox.confirm("Are you sure to create a folder: "+fodlerName.value+", under "+sendData.curerntDirectory, function(ok){ if(ok){
        // if(confirm("Are you sure to create a folder: "+fodlerName.value+", under "+sendData.curerntDirectory)){
        thisObj._ajaxService.AjaxSubscribe('site/create-folder',sendData,(result)=>
        {  
           
            fodlerName.value="";
           thisObj.repo =thisObj.prepareFileStructure(result.data);
           })
        // }
        } 
    });
        }
    }
    
  
    
    showLog(){
    var sendData={
           userId:this.users.Id,
           repName:this.projName,
           userName:this.users.username,
           ProjectId:this.projId,
           password:'minimum8',
           role:'RW'
           };
           
         this._ajaxService.AjaxSubscribe('site/show-svnlog',sendData,(result)=>
        {
        
           this.svnLogs = result.data;
      
           })
    }

    prepareTeamPermissionsData(teamData=[]){
        var teamPermissions = [];
        for(let teamMember of teamData){
            var read = false;
            var write = false;
            switch(teamMember.role){
                case "R":
                read = true;
                break;
                case "RW":
                write = true;
                read = true;
                break;
                case "REM":
                write = false;
                read = false;
                break;
            }
            teamPermissions.push({id:teamMember.Id,userName: teamMember.Name,ProfilePic:teamMember.ProfilePic, read:read, write:write, modified:false});
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
        var thisObj = this;
        // alert("----permissions----"+JSON.stringify(sendData));
        bootbox.confirm("Are you sure to save these permissions?", function(ok){ if(ok){
        // if(confirm("Are you sure to save these permissions?")){
        thisObj._ajaxService.AjaxSubscribe('site/create-user',sendData,(result)=>
        {

           console.log("----repodata----"+JSON.stringify(result));

           })
        }
    });
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

    premissionsChanges(memberObj){
        // alert("changes===>"+JSON.stringify(memberObj));
        memberObj.modified = true;
        setTimeout(()=>{
        if(memberObj.write){
            memberObj.read=true;
        }
        // alert("changes=after==>"+JSON.stringify(memberObj));
        },200);
        
    }
}
