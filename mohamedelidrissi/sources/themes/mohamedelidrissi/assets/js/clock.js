// the clock object
var clock = new Object();
clock.init = window.setInterval(function () { clock.initialize() }, 50);

// initialize clock
clock.initialize = function () {
  if (document.getElementById('bgLast') &&
    document.getElementById('dial') &&
    document.getElementById('hourHand') &&
    document.getElementById('minuteHand') &&
    document.getElementById('secondHand') &&
    document.getElementById('axisCover')) {

    this['bgLastColor'] = "#2EAB3B";
    this['dialColor'] = "#167175";
    this['hourHandColor'] = "#001d1f";
    this['minuteHandColor'] = "#001d1f";
    this['secondHandColor'] = "#f00";
    this['axisCoverColor'] = "#001d1f";
    this['bgOrnamentColor'] = "#E2F1F3";
    this['axisCoverRadius'] = "10";
    this['bgOrnament'] = "muhammad";
    this['dial'] = "austria";
    this['hourHand'] = "siemens";
    this['minuteHand'] = "siemens";
    this['secondHand'] = "siemens";
    // get html parameter and set clock attributes
    if (document.defaultView.frameElement) {
      var params = document.defaultView.frameElement.getElementsByTagName('param');
      for (var i = 0; i < params.length; i++) {
        this[params[i].name] = params[i].value.toLowerCase();
      }
    }

    // set clock colors
    this.setColorForElement('bgLast');
    this.setColorForElement('dial');
    this.setColorForElement('hourHand');
    this.setColorForElement('minuteHand');
    this.setColorForElement('secondHand');
    this.setColorForElement('axisCover');
    this.setColorForElement('bgOrnament');

    // set clock elements
    this.setClockDial(this.dial);
    this.setHourHand(this.hourHand);
    this.setMinuteHand(this.minuteHand);
    this.setSecondHand(this.secondHand);
    this.setAxisCover(this.axisCoverRadius);
    this.setOrnament(this.bgOrnament);
    this.setBgLast(true);

    // draw clock
    this.draw();

    // show clock
    this.showElement('clock', true);

    // finish initialization and start animation loop
    window.clearInterval(this.init);
    var that = this;
    window.setInterval(function () { that.draw() }, isNaN(this.updateInterval) ? 50 : this.updateInterval);
  }
}

// draw the clock
clock.draw = function () {
  var now = new Date();
  var hours = now.getHours();
  var minutes = now.getMinutes();
  var seconds = now.getSeconds();
  var millis = now.getMilliseconds();

  // rotate hour hands
  this.rotateElement('hourHand', 30 * hours + 0.5 * minutes);

  // rotate minute hand
  this.rotateElement('minuteHand', 6 * minutes + (this.minuteHandBehavior === 'sweeping' ? 0.1 * seconds : 0));

  // handle "stop to go" second hand
  if (this.secondHandStopToGo === 'yes' || this.secondHandStopToGo === 'true') {
    var wait = isNaN(this.secondHandStopTime) ? 1.5 : this.secondHandStopTime;
    var fact = 60 / (60 - Math.min(30, Math.max(0, wait)));
    var time = Math.min(60000, fact * (1000 * seconds + millis));
    seconds = Math.floor(time / 1000);
    millis = time % 1000;
  }

  // rotate second hand
  var secondAngle = 6 * seconds;
  if (this.secondHandBehavior === 'sweeping') {
    secondAngle += 0.006 * millis;
  } else if (this.secondHandBehavior === 'swinging') {
    secondAngle += 3 * (1 + Math.cos(Math.PI + Math.PI * (0.001 * millis)));
  }
  this.rotateElement('secondHand', secondAngle);
}

// set element fill and stroke color
clock.setColorForElement = function (id) {
  var element = document.getElementById(id);
  var color = this[id + 'Color'];
}

// set clock dial
clock.setClockDial = function (value) {
  this.showElement('dialSwiss', value === 'swiss' || value === undefined);
}

// set hour hand
clock.setHourHand = function (value) {
  this.showElement('hourHandSwiss', value === 'swiss' || value === undefined);
}

// set minute hand
clock.setMinuteHand = function (value) {
  this.showElement('minuteHandSwiss', value === 'swiss' || value === undefined);
}

// set second hand
clock.setSecondHand = function (value) {
  this.showElement('secondHandSwiss', value === 'swiss' || value === undefined);
}

// set axis cover
clock.setAxisCover = function (value) {
  document.getElementById('axisCoverCircle').setAttribute('r', isNaN(value) ? 0 : value);
}

// set background ornament visibility
clock.setOrnament = function (value) {
  this.showElement('bgMuhammad', value === 'muhammad');
}

// set background visibility
clock.setBgLast = function (value) {
  this.showElement('bgLast', value);
}

// show clock element
clock.showElement = () => { }

// rotate clock element
clock.rotateElement = function (id, angle) {
  document.getElementById(id).setAttribute('transform', 'rotate(' + angle + ', 100, 100)');
}
