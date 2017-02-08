var Express = require("express");
var bodyParser = require("body-parser");
var multer = require("multer");
var uploads = multer({ dest: 'uploads/' });
//app.use(multer({dest:__dirname+'/file/uploads/'}));
var app = Express();

var type = uploads.array("uploads[]", 12);

console.log("entry");
 
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
 
app.use(function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

app.post("/upload", type, function(req, res, cb) {
//console.log("the file-------- " + req.hostname);
    
    if (!req.files) {
        res.send('No files were uploaded.');
    } else{
        //console.log("the responce -------- " + req.files + " --- " + JSON.stringify(req.files));
        //console.log( "the new file name " + typeof JSON.stringify(req.files['filename']) + "|" + typeof req.files.originalname );
        res.send(req.files);
    }
    
});
 
var server = app.listen(4200, function() {
    console.log("Listening on port %s...", server.address().port);
});
