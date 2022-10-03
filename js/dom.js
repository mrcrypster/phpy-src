// Query selector with callback
function qs(selector, callback) {
  let found = document.querySelectorAll(selector);

  if ( callback ) {
    found.forEach(function(el) { callback.apply(el, [el]); });
  }

  return found;
}


// Attach events using query selector
function on(selector, event, callback) {
  qs(selector).forEach(function(el) {
    el.addEventListener(event, callback);
  });
}