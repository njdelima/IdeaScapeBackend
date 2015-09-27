var watson = require('watson-developer-cloud');

var concept_insights = watson.concept_insights({
	username: 'd5f7aa9a-f6ce-4a8c-9355-b142f430c785',
	password: 'MJX4EfaWkktM',
	version: "v2"
});

var express = require('express');
var bodyParser = require('body-parser');
var cors = require('cors');
var app = express();

app.use(bodyParser.json({limit: '50mb'}));
app.use(bodyParser.urlencoded({ limit: '50mb', extended: true }));
app.use(cors());
app.use(function(req, res, next) {
  res.header("Access-Control-Allow-Origin", "*");
  res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
  next();
});

var server = app.listen(2003, function() {
	
	var host = server.address().address;
	var port = server.address().port;
	console.log("watson.js listening at http://%s:%s", 'localhost', port);
});

app.post('/', function(req, res) {
	var params = {
		graph: '/graphs/wikipedia/en20120601',
		text: req.body.key1
	};

	console.log("STARTED watson.js")
	
	concept_insights.graphs.annotateText(params, function(err, response) {
		if (err) {
			res.send(err);
		} else {
			res.send(response);
		}
	});
});
