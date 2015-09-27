var watson = require('watson-developer-cloud');

var concept_insights = watson.concept_insights({
	username: 'c26ddad4-72d1-459b-853e-c8b5dbf2cb53',
	password: 'Bbj4lcJOYVnO',
	version: "v2"
});

var express = require('express');
var bodyParser = require('body-parser');
var cors = require('cors');
var app = express();

app.use(bodyParser.urlencoded({ extended: false }));
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
