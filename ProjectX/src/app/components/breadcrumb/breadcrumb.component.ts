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
    this.isLoggedOut=true;
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
        console.log("==local storage=="+localStorage.getItem('ProjectName'));
        if(this.route_changes.page!='Logout')
        {
          if(this.items.length>0 && this.count==0 && this.route_changes.page!='Detail' && this.route_changes.type!='Other')
                {
                   for (var key in this.items[0]) 
                   {
                    this.items[0].url="/#/project/"+this.route_changes.params+"/list";
                    this.items[0].label=this.route_changes.params;
                   }
                }
          if(this.count==0 && this.status!=true && localStorage.getItem('ProjectName')!='')
            {
              console.log("==Length=="+this.items.length);
              console.log("==projectName=="+localStorage.getItem('ProjectName'));
              // this.removeItems(0,false);
              if(this.route_changes.params!=null)
              {
                
                 this.items.push({label:localStorage.getItem('ProjectName'),url:"/#/project/"+localStorage.getItem('ProjectName')+"/list"});
                
                this.status=true;
              }
            }
            // console.log("==Index=="+this.id.indexOf('#'+this.route_changes.params));
            // if(this.id.indexOf('#'+this.route_changes.params)>-1)
            // {
            //   this.removeItems(this.id.indexOf('#'+this.route_changes.params)+2,true);
            // }

            

            
            if((this.route_changes.page=='Detail') && !(this.id.indexOf('#'+this.route_changes.params)>-1))
            {
              this.items.push({label:'#'+this.route_changes.params,url:"/#"+this.route_changes.url,type:this.route_changes.type});
              this.id.push('#'+this.route_changes.params);
              this.count++;
              console.log("==Details Count=="+this.count);
             // this.removeItems(this.count-1,true); //added newly by Ryan
            }

            /*For Back Logic */
            for (var key in this.items) 
                   {
                     if((this.items[key].label==this.route_changes.params) || (this.items[key].label=='#'+this.route_changes.params) )
                     {
                       console.log("==Key=="+key);
                       //this.removeItems(key+1,true);
                       for(var i:any=parseInt(key);i<this.items.length;i++)
                        {
                          //this.items[i].remove();
                          this.items.splice(i+1,this.items.length);
                          this.id.splice(i,this.items.length);
                        }
                     }
                   }
            /*Back Logic End */
            
        // }
        //  else{ //for search  and notifications
          if(this.route_changes.type=='Other'){ 
              this.status=true;
              console.log("==Id length=="+this.id.length);
              if(this.id.length!=0)
              {        
                this.removeItems(1,true);
                this.items.push({label:this.route_changes.page,url:"/#"+this.route_changes.url});
                
              }
              else
              {
                this.items.push({label:this.route_changes.page,url:"/#"+this.route_changes.url});
                if(this.items.length>1)
                {
                  for (var key in this.items) 
                    {
                    
                      if(this.items[0].label==this.items[1].label)
                      {
                        this.removeItems(1,true);
                      }
                    }
                }
               
                // if(this.count>1 && this.route_changes.page!='TimeReport')
                // {
                //   this.removeItems(1,true);
                // }
              }
          }
          if(this.route_changes.page=='Home')
          {
             this.status=true;        
             this.removeItems(0,false);
          }
          this.isLoggedOut=false;
        }
        else{
              console.log("==Page after Logout=="+this.route_changes.page);
              this.id=[];
              this.count=0;
              this.status=false;
              //this.status=true;
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
    console.log(index);
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
      // if(this.items.length==0)
      // {
        this.id=[];
        this.count=0;
        this.status=status;
      //}
  }

}