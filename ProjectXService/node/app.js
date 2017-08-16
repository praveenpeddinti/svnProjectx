var Express = require("express");
var spawn = require('child_process').spawn;
var bodyParser = require("body-parser");
var multer = require("multer");
var uploads = multer({ dest: '/usr/share/nginx/www/ProjectXService/node/uploads/' });
var uploadImages = multer({ dest: '/usr/share/nginx/www/ProjectXService/frontend/web/files/temp' });
//app.use(multer({dest:__dirname+'/file/uploads/'}));
var app = Express();

var type = uploads.array("uploads[]", 12);
var typeImage = uploadImages.array("uploads[]", 12);
var dir = "/usr/share/nginx/www/ProjectXService";
var exec = require('child_process').exec;
var child;
console.log("entry");
 var globalArray = [];

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
 
app.use(function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

app.post("/upload", type, function(req, res, cb) {
console.log("the file-------- " );
    
    if (!req.files) {
        res.send('No files were uploaded.');
    } else{
       // console.log("the responce -------- " + req.files[0].path + " --- " + JSON.stringify(req.files));
     for(var i=0;i<req.files.length;i++){
        var path = req.files[i].path;
        path = path.replace("/usr/share/nginx/www/ProjectXService/node/", "");
        req.files[i].path = path; 
     }
        res.send(req.files);
    }
    
});
app.post("/uploadImage", typeImage, function(req, res, cb) {
console.log("the uploadImage-------- " );
    
    if (!req.files) {
        res.send('No files were uploaded.');
    } else{
       // console.log("the responce -------- " + req.files[0].path + " --- " + JSON.stringify(req.files));
     for(var i=0;i<req.files.length;i++){
        var path = req.files[i].path;
        path = path.replace("/usr/share/nginx/www/ProjectXService/frontend/web/", "");
        req.files[i].path = path; 
     }
        res.send(req.files);
    }
    
});




app.post("/assignedTo",function(req, res, cb) {
//console.log(req.body+"----assignedTo-------- "+req+"-----"+req.body);
var request = req.body;
console.log("===collaborator=="+request.ticketId);
  child=spawn(dir+"/yii",['notifications/assigned-notify',JSON.stringify(request)]);
        child.stdout.setEncoding('utf-8');
        child.stdout.on('data', function(data) {
          console.log("assignedTo-------result- " );
             res.send(data);
       });
          child.stderr.on('data', function(data) {
//            logger.trace('stderr: ' + data);
            console.log("assignedTo-------error- "+data );
       });
    
});

app.post("/follow",function(req, res, cb) {
//console.log(req.body+"----assignedTo-------- "+req+"-----"+req.body);
var request = req.body;
console.log("===collaborator=="+request.ticketId);
  child=spawn(dir+"/yii",['notifications/follow-notify',JSON.stringify(request)]);
        child.stdout.setEncoding('utf-8');
        child.stdout.on('data', function(data) {
          console.log("assignedTo-------result- " );
             res.send(data);
       });
          child.stderr.on('data', function(data) {
//            logger.trace('stderr: ' + data);
            console.log("assignedTo-------error- "+data );
       });
    
});

app.post("/getAllNotifications",function(req,res,cb)
{
    var request = req.body;
    console.log("data____REQ___IN____NODE___"+JSON.stringify(request));
  child=spawn(dir+"/yii",['notifications/get-all-notifications',JSON.stringify(request)]);
        child.stdout.setEncoding('utf-8');
        child.stdout.on('data', function(data) {
            if(data!="" && data!=0 && data!=null){
                console.log("getAllNotifications-- " +data+"---"+data.length);
                  res.send(data);  
            }
       });
          child.stderr.on('data', function(data) {
//            logger.trace('stderr: ' + data);
            console.log("assignedTo-------error- "+data );
       });
});


app.post("/propertyChange",function(req,res,cb)
{
    var request = req.body;
  child=spawn(dir+"/yii",['notifications/change-property',JSON.stringify(request)]);
        child.stdout.setEncoding('utf-8');
        child.stdout.on('data', function(data) {
          console.log("assignedTo-------result- " +data);
             res.send(data);
       });
          child.stderr.on('data', function(data) {
//            logger.trace('stderr: ' + data);
            console.log("assignedTo-------error- "+data );
       });
});





 var http = require('http').createServer(app);
 var server = app.listen(4201);
 var io = require('socket.io')(server);
 console.log("in");
    io.sockets.on('connection', function(client)
    {   console.log('Connection started---------------------'+client.id);

        client.on('getAllNotificationsCount', function(request) {
            console.log('******************getAllNotificationsCount1***************'+JSON.stringify(request));
     getUnreadNotificationsCount(request,client);
    
    clearInterval(globalArray[client.id]);
     var interval = setInterval(function(){
          console.log("--pining interval----");
           getUnreadNotificationsCount(request,client);
        },15000)
     globalArray[client.id] = interval;

       });
       
        function getUnreadNotificationsCount(request,client){
                      child=spawn(dir+"/yii",['notifications/get-all-notifications-count',JSON.stringify(request)]);
            child.stdout.setEncoding('utf-8');
            child.stdout.on('data', function(data) {
              console.log("get-all-notifications-count-- " +data);
                if(data!="" && data!=0 && data!=null){
                     client.emit("getAllNotificationsCountResponse",data);
                }
                
           });
              child.stderr.on('data', function(data) {
    //            logger.trace('stderr: ' + data);
                console.log(client.id+"----getUnreadNotificationsCount-------error- "+data );
           });
    }
    
    client.on('getLatestActivities', function(request) {
            console.log('******************getLatestActivities***************'+JSON.stringify(request));
     getNewActivity(request,client);
    
    clearInterval(globalArray[client.id]);
     var interval = setInterval(function(){
          console.log("--pining interval----Activity");
           getNewActivity(request,client);
        },100000)
     globalArray[client.id] = interval;

       });
       
         function getNewActivity(request,client){
                      child=spawn(dir+"/yii",['notifications/get-latest-activity',JSON.stringify(request)]);
            child.stdout.setEncoding('utf-8');
            child.stdout.on('data', function(data) {
              console.log("get-latest-activity-- " +data);
                if(data!="" && data!=0 && data!=null){
                     client.emit("getLatestActivitiesResponse",data);
                }
                
           });
              child.stderr.on('data', function(data) {
    //            logger.trace('stderr: ' + data);
                console.log(client.id+"----getLatestActivities-------error- "+data );
           });
    }
    
     client.on('clearInterval', function()
    {
//        logger.trace('Client disconnected');
       console.log('Client clearInterval-- '+client.id);
       clearInterval(globalArray[client.id]);
        
    });
 client.on('disconnect', function()
    {
//        logger.trace('Client disconnected');
       console.log('Client disconnected-- '+client.id);
       clearInterval(globalArray[client.id]);
        
    });
    });
    
   