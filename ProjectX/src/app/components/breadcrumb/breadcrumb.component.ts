import {Component,OnInit,Input} from '@angular/core';
import {ActivatedRoute,Router,NavigationEnd,Params,PRIMARY_OUTLET,UrlTree,UrlSegment,UrlSegmentGroup} from '@angular/router';
import {MenuItem} from 'primeng/primeng';
import { GlobalVariable } from '../../config';
import {SharedService} from '../../services/shared.service'; //this service updates the component variable from another component

declare var jQuery:any;
@Component({
  selector: 'breadcrumb',
  templateUrl:'./breadcrumb.component.html',
  styleUrls: ['./breadcrumb.component.css'],
})

export class BreadcrumbComponent implements OnInit {
  
  private items:any=[];
  private route_url:string;
  private route_params:string;
  private path='';
  private id:any=[];
  private route_changes;
  private count=0;
  private status=false;
  private isLoggedOut=false;
  constructor(private router:Router,private route:ActivatedRoute,private shared:SharedService){
    this.shared=shared;
    console.log("==in const==");
  }
  ngOnInit()
  {
    
    var path_url;
    console.log("==In breadcrumb==");
      this.shared.getEmittedValue().subscribe(value=>
      {
        this.route_changes=value; //params from URL
        console.log("==Count=="+this.count);
        console.log("==Value in BreadCrumb=="+this.route_changes.url +' '+this.route_changes.params);
        if(this.route_changes.page!='Logout')
        {
          if(this.count==0 && this.status!=true && localStorage.getItem('ProjectName')!='')
            {
              console.log("==Length=="+this.items.length);
              console.log("==projectName=="+localStorage.getItem('ProjectName'));
            // this.removeItems(0,false);
              this.items.push({label:localStorage.getItem('ProjectName'),url:"/#/project/"+localStorage.getItem('ProjectName')+"/list"});
              this.status=true;
            }
            if((this.route_changes.page=='Detail') && !(this.id.indexOf('#'+this.route_changes.params)>-1))
            {
              this.items.push({label:'#'+this.route_changes.params,url:"/#"+this.route_changes.url,type:this.route_changes.type});
              this.id.push('#'+this.route_changes.params);
              this.count++;
            }
            
        // }
        //  else{ //for search  and notifications
          if(this.route_changes.page == "Search" || this.route_changes.page=="Notifications" || this.route_changes.page=='Error'){ 
              this.status=true;        
              this.removeItems(1,true);
              this.items.push({label:this.route_changes.page,url:"/#"+this.route_changes.url});
          }
          if(this.route_changes.page=='Home')
          {
             this.status=true;        
             this.removeItems(1,true);
          }
          this.isLoggedOut=false;
        }
        else{
              console.log("==Page after Logout=="+this.route_changes.page);
              this.id=[];
              this.count=0;
              this.status=false;
              this.isLoggedOut=true;
              this.items=[];
            }
         
     // }

      });

      // this.items.push({url:'/story-detail/317',label:'#317'});
      //  this.items.push({url:'/story-detail/318',label:'#318'});

  }
  
  modifyBreadcrumb(index)
  {
    if(index==0)
    {
      this.removeItems(index+1,true);
    }
    else if(index==null)
    {
      this.removeItems(0,false);
    }
    else 
    {
      this.removeItems(index,true);
    }
  }

  removeItems(index,status)
  {
    for(var i=index;i<this.items.length;i++)
      {
        //this.items[i].remove();
        this.items.splice(i,this.items.length);
      }
      this.id=[];
      this.count=0;
      this.status=status;
  }

}