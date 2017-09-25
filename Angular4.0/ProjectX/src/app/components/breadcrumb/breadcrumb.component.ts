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
  private project;
  constructor(private router:Router,private route:ActivatedRoute,private shared:SharedService){
    this.shared=shared;
   // console.log("==in const==");
    this.isLoggedOut=true;
  }
  ngOnInit()
  {
    
    var path_url;
   // console.log("==In breadcrumb==");
      this.shared.getEmittedValue().subscribe(value=>
      {
        
        this.route_changes=value; //params from URL
       // console.log("==Count=="+this.count);
      //  console.log("==Value in BreadCrumb=="+this.route_changes.url +' '+this.route_changes.params);
      //  console.log("==local storage=="+localStorage.getItem('ProjectName'));
      this.project=this.route_changes.projectName;
        if(this.route_changes.page!='Logout')
        {
          if(this.items.length>0 && this.count==0 && this.route_changes.page!='Detail' && this.route_changes.type!='Other')
                {
                   for (var key in this.items[0]) 
                   {
                    this.items[0].url="/project/"+this.route_changes.params+"/list";
                    this.items[0].label=this.route_changes.params;
                   }
                }
                if(this.route_changes.type=="New" && this.items.length>0)
                    {
                    //  console.log("in new"+this.items.length);
                      this.items.push({label:this.route_changes.type,url:"/project/"+this.route_changes.params+"/new",queryString:''});
                    //  console.log("in new"+this.items.length);
                    }
          if(this.route_changes.projectName!=''  && this.status!=true && this.project!='') //newly changed
            {
            //////  console.log("==Length=="+this.items.length);
            ///  console.log("==projectName=="+this.route_changes.projectName);
              // this.removeItems(0,false);
             if(this.id.length==1 && this.items.length==1) //newly added for The Notification Use Case
             {
               this.removeItems(0,false);
             }
                 if(this.route_changes.projectName!=undefined)
                 { 
                  this.items.push({label:this.route_changes.projectName,url:"/project/"+this.route_changes.projectName+"/list",queryString:''});
                
                this.status=true;
                this.project=this.route_changes.projectName;
                 }
              }
                 
            if((this.route_changes.page=='Detail') && !(this.id.indexOf('#'+this.route_changes.params)>-1))
            { 
            //  for (var key in this.items[0]) 
                 //  { 
                   if( this.items[0].type != "Other"){

                    this.items[0].url="/project/"+this.route_changes.projectName+"/list";
                    this.items[0].label=this.route_changes.projectName;
                    this.items[0].queryString='';
                    }
                    if(this.project!=this.route_changes.projectName && this.project!='')
                    {
                    //  console.log("==not equal==");
                      //this.id=[];
                      this.items.splice(1,this.items.length);
                      this.project='';
                      this.status=false;
                    }
                 //  }
              this.items.push({label:'#'+this.route_changes.params,url:"/"+this.route_changes.url,type:this.route_changes.type,queryString:''});
              this.id.push('#'+this.route_changes.params);
              this.count++;
            //  console.log("==Details Count=="+this.count);
             // this.removeItems(this.count-1,true); //added newly by Ryan
            }

            /*For Back Logic */
            for (var key in this.items) 
                   {
                     if((this.items[key].label==this.route_changes.params) || (this.items[key].label=='#'+this.route_changes.params || this.route_changes.params==''))
                     {
                    //   console.log("==Key=="+key);
                       //this.removeItems(key+1,true);
                       for(var i:any=parseInt(key);i<this.items.length;i++)
                        {
                          //this.items[i].remove();
                          if(this.route_changes.params=='')
                          {
                            this.removeItems(0,false);
                          }
                          else
                          {
                            this.items.splice(i+1,this.items.length);
                            this.id.splice(i,this.items.length);
                          }
                        }
                     }                 
                   }
                   for(var key in this.items)
                   {
                      if(this.route_changes.type=="New" && this.items.length>0)
                      {
                        if(this.items[key].label!=this.route_changes.type)
                        {
                          this.items.push({label:this.route_changes.type,url:"/project/"+this.route_changes.params+"/new",queryString:''});
                        }
                      }
                   }
            /*Back Logic End */
            
        // }
        //  else{ //for search  and notifications
          if(this.route_changes.type=='Other'){ 
              this.status=true;
            //  console.log("==Id length=="+this.id.length);
              if(this.id.length!=0)
              {      
                this.removeItems(1,true);
                var url = this.route_changes.url;
                var urlPart = url.split("?")[0];
                this.items.push({label:this.route_changes.page,type:this.route_changes.type,url:"/"+urlPart,queryString:this.route_changes.params});
                
              }
              else
              { 
              var url = this.route_changes.url;
              // var arrayQueryString = this.getUrlVars(url);
               var urlPart = url.split("?")[0];
                this.items.push({label:this.route_changes.page,type:this.route_changes.type,url:"/"+urlPart,queryString:this.route_changes.params});
                if(this.items.length>1)
                {
                //  console.log("==Items Length=="+this.items.length);
                  for(var key in this.items) 
                    {
                  //    console.log("== Count key ===="+key);
                      var i:any=parseInt(key);
                      if(i<this.items.length)
                      {
                    //    console.log("condition satisfied");
                        if(this.items[i].label==this.route_changes.page)
                        {
                     //     console.log("condition satisfied");
                          this.removeItems(i+1,true);
                        }
                      }
                    }
                }
               
              }
          }
          if(this.route_changes.page=='Home')
          {
             this.status=true;        
             this.removeItems(0,false);
          }
          if(this.route_changes.navigatedFrom=="Notification") //newly added by Ryan
          {
           // console.log("navigated from");
            for (var key in this.items)
            {
              // if(this.items[key].label=="Search")  // correct Use Case for Notification,need to verify with Moin
              //         {
              //           this.items.splice(parseInt(key),1);
              //         }
              this.removeItems(0,false);
              this.items.push({label:'#'+this.route_changes.params,url:"/"+this.route_changes.url,type:this.route_changes.type,queryString:''});
              this.id.push('#'+this.route_changes.params);
            }
          }
          this.isLoggedOut=false;
        }
        else{
            //  console.log("==Page after Logout=="+this.route_changes.page);
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
  //  console.log(index);
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
     // this.removeItems(index,true);
     this.removeItems(index+1,true); //newly added
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
 getUrlVars(url)
{
    var vars = [], hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
}