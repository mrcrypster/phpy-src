/* Pub/sub component */

var pubsub = {}

function pub(event, data) {
  if ( pubsub[event] ) {
    pubsub[event].forEach(function(cb) {
      cb(data);
    });
  }
}

function sub(event, callback) {
  if ( !pubsub[event] ) {
    pubsub[event] = [];
  }

  pubsub[event].push(callback);
}