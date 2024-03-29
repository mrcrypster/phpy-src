/* PHPy [server] component execution & render */

function phpy(com, data, callback) {
  data = data || {};
  if ( typeof data != 'object' ) {
    data = {value: data};
  }

  if ( typeof this.dataset == 'object' ) {
    for ( var k in this.dataset ) {
      data[k] = this.dataset[k];
    }
  }
  
  if ( data instanceof HTMLFormElement ) {
    data = new FormData(data);
  }
  else if ( !(data instanceof FormData) ) {
    data = new URLSearchParams(data);
  }
  
  fetch(com, {
    method: 'post',
    body: data
  }).then(function(r) {
    if ( r.headers.get('Xlocation') ) {
      location = r.headers.get('Xlocation');
      return;
    }

    if ( r.headers.get('Xpub') ) {
      let events = JSON.parse(atob(r.headers.get('Xpub')));
      for ( let e in events ) {
        pub(e, events[e]);
      }
    }
    
    return r.json();
  }).then(function(r) {
    for ( let k in r ) {
      qs(k, (e) => e.innerHTML = r[k]);
    }

    if ( typeof(callback) != 'undefined' ) {
      callback.apply(this, [r]);
    }
  });
}
