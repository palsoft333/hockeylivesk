/**
 * [jquery.calendario.js] (v4.1.0) #Copyright 2015, Boží Ďábel#
 * Updated by Palsoft on line 363
 */

+function ($) {
  'use strict';

  var Calendario = function (element, options) {
    this.init('calendario', element, options)
  }

  Calendario.INFO = {
    EMAIL : '%email%',
	FEED : '%feed%',
	NAME : 'FrozenTime!',
	VERSION : '4.1.0',
	UNIQUE : '%unique%',
	USER : '%user%',
	UPDATEURL : '%url%'
  }

  Calendario.DEFAULTS = {
    weeks : ['Nedeľa', 'Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota'],
    weekabbrs : ['Ne', 'Po', 'Ut', 'St', 'Št', 'Pi', 'So'],
    months : ['Január', 'Február', 'Marec', 'Apríl', 'Máj', 'Jún', 'Júl', 'August', 'September', 'Október', 'November', 'December'],
    monthabbrs : ['Jan', 'Feb', 'Mar', 'Apr', 'Máj', 'Jún', 'Júl', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
    displayWeekAbbr : false,
    displayMonthAbbr : false,
    startIn : 1,
    fillEmpty: true,
    zone: '00:00',
	events : ['click', 'focus', 'hover'],
    checkUpdate: false,
    weekdays: 'PON, UTO, STR, STV, PIA',
    weekends: 'SOB, NED'
  }

  Calendario.prototype.init = function (type, element, options) {
    this.INFO      = Calendario.INFO
    this.type      = type
    this.$element  = $(element)
    this.options   = $.extend({}, Calendario.DEFAULTS, this.$element.data(), options)
    this.today     = new Date()
    this.month     = (isNaN(this.options.month) || this.options.month === null) ? this.today.getMonth() : this.options.month - 1
    this.year      = (isNaN(this.options.year) || this.options.year === null) ? this.today.getFullYear() : this.options.year
    this.caldata   = this.processCaldata(this.options.caldata)
    this.curData   = []
    this.syncData  = {}
    this.generateTemplate()
    this.initEvents()
  }

  Calendario.prototype.sync = function (data) {
    var self = this
    $.post(self.options.feed, {info: self.INFO, caldata: data, domain: document.domain}, function(d){ self.syncData = d }, 'json')
    return data
  }

  Calendario.prototype.initEvents = function () {
    this.$element.on(this.options.events.join('.calendario ').trim() + '.calendario', 'div.row > div:not(:empty)', function(e) {
      $(this).trigger($.Event('onDay' + e.type.charAt(0).toUpperCase() + e.type.slice(1)), [$(this).data('bz.calendario.dateprop')])
    })
  }
  
  Calendario.prototype.propDate = function () {
    var self = this
    this.$element.find('div.row > div').filter(':not(:empty)').each(function() {
      var dateProp = {
        day : $(this).children('span.fc-date').text(),
        month : self.month + 1,
        monthname : self.options.displayMonthAbbr ? self.options.monthabbrs[self.month] : self.options.months[self.month],
        year : self.year,
        weekday : $(this).index() + self.options.startIn,
        weekdayname : self.options.weeks[($(this).index() == 6 ? 0 : $(this).index() + self.options.startIn)],
        data : self.curData[$(this).children('span.fc-date').text()] ? self.curData[$(this).children('span.fc-date').text()] : false
      }
      $(this).data('bz.calendario.dateprop', dateProp)
    })
  }

  Calendario.prototype.insertToCaldata = function(key, c, date, data) {
    if(!data[key]) data[key] = []
    c.repeat ? c.day = [date[1], c.endDate.split('-')[1]] : c.day = [date[1], date[1]]
    c.repeat ? c.month = [date[0], c.endDate.split('-')[0]] : c.month = [date[0], date[0]]
    c.repeat ? c.year = [date[2], c.endDate.split('-')[2]] : c.year = [date[2], date[2]]
    c.category = c.category ? 'calendar-' + c.category.split('-').pop() : 'calendar-default list-group-item list-group-item-action small p-2 text-center'
    return data[key].push(c) ? data : data
  }

  Calendario.prototype.processCaldata = function (obj) {
    var data = {}, self = this
    $.each(obj, function(key, val){
      $.each(val, function(i, c){
        if(c.repeat == 'INTERVAL' || c.repeat == 'EVERYDAY') c.repeat = 'MON, TUE, WED, THU, FRI, SAT, SUN'
        else if(c.repeat == 'WEEKDAYS') c.repeat = self.options.weekdays
        else if(c.repeat == 'WEEKENDS') c.repeat = self.options.weekends
        if($.inArray(c.repeat, [undefined, 'YEARLY', 'MONTHLY']) != -1) data = self.insertToCaldata(parseInt(key.split('-')[1]), c, key.split('-'), data)
        else if(c.repeat) {
          $.each(c.repeat.split(','), function(v, k){
            data = self.insertToCaldata(k.trim(), $.extend(c, {repeat: 'WEEKLY'}), key.split('-'), data)
          })
        }
      })
    })
    return self.sync(data)
  }

  Calendario.prototype.toDObj = function(time, day) {
    var zoneH = parseInt(this.options.zone.split(':')[0])
    var zoneM = parseInt(this.options.zone.charAt(0) + this.options.zone.split(':')[1])
    var hour = parseInt(time.split(':')[0]) - zoneH
    var minutes = parseInt(time.split(':')[1]) - zoneM
    return new Date(Date.UTC(this.year, this.month, day, hour, minutes, 0, 0))
  }

  Calendario.prototype.parseDay = function(c, day) {
    if(!this.curData[day]) this.curData[day] = {html: [], allDay: [], startTime: [], endTime: [], note: [], content: [], url: [], color: []}
    c.allDay  ? this.curData[day].allDay.push(true) : this.curData[day].allDay.push(false)
    c.allDay  ? this.curData[day].startTime.push(this.toDObj('00:00', day)) : this.curData[day].startTime.push(this.toDObj(c.startTime, day))
    c.allDay  ? this.curData[day].endTime.push(this.toDObj('23:59', day)) : this.curData[day].endTime.push(this.toDObj(c.endTime, day))
    c.note    ? this.curData[day].note.push(c.note) : this.curData[day].note.push('')
	c.content ? this.curData[day].content.push(c.content) : this.curData[day].content.push('')
	c.url     ? this.curData[day].url.push(c.url) : this.curData[day].url.push('')
	c.color     ? this.curData[day].color.push(c.color) : this.curData[day].color.push('')
    var i = c.url ? this.curData[day].html.push('<a class="' + c.category + '" href="' + c.url + '">' + c.content +'</a>') - 1
                  : this.curData[day].html.push('<span class="' + c.category + '">' + c.content + '</span>') - 1
    this.curData[day].html[i] += '<time class="fc-allday" datetime="' + this.curData[day].allDay[i] + '"></time>'
    this.curData[day].html[i] += '<time class="fc-starttime" datetime="' + this.curData[day].startTime[i].toISOString() + '"></time>'
    this.curData[day].html[i] += '<time class="fc-endtime" datetime="' + this.curData[day].endTime[i].toISOString() + '"></time>'
    this.curData[day].html[i] += '<note>' + this.curData[day].note[i] + '</note>'
  }
  
  Calendario.prototype.parseDayID = function(c, day) {
    if(!this.curData[day]) this.curData[day] = {html: [], allDay: [], startTime: [], endTime: [], note: [], content: [], url: [], color: []}
	c.color     ? this.curData[day].color.push(c.color) : this.curData[day].color.push('')
  }

  Calendario.prototype.parseDataToDay = function(data, day) {
    var self = this
    $.each(data, function(i, c) {
      if(!c) {/*ignore*/}
      else if(c.repeat == 'YEARLY' || c.repeat == 'MONTHLY' || c.repeat == 'WEEKLY') {
        if(self.year >= c.year[0] && self.year <= c.year[1]) {
          if(c.repeat == 'YEARLY' && (self.month + 1) == c.month[0]) self.parseDay(c, day)
          if(self.year == c.year[0] && (self.month + 1) >= c.month[0]) {
            if(c.repeat == 'MONTHLY') self.parseDay(c, day)
            if(c.repeat == 'WEEKLY') {
              if(c.month[0] + c.year[0] == c.month[1] + c.year[1]) {
                if(c.month[0] == (self.month + 1) && c.day[1] >= day && day >= c.day[0]) self.parseDay(c, day)
              } else if(c.month[0] == (self.month + 1) && day >= c.day[0]) self.parseDay(c, day)
              else if(c.year[0] == c.year[1] && c.month[1] > (self.month + 1) && c.month[0] < (self.month + 1)) self.parseDay(c, day)
              else if(c.year[0] == c.year[1] && c.month[1] == (self.month + 1) && day <= c.day[1]) self.parseDay(c, day)
              else if(c.year[0] != c.year[1] && c.month[0] < (self.month + 1)) self.parseDay(c, day)
            }
          } else if(c.year[0] < self.year && self.year < c.year[1]) {
            if(c.repeat == 'MONTHLY' || c.repeat == 'WEEKLY') self.parseDay(c, day)
          } else if((self.month + 1) <= c.month[1] && self.year == c.year[1]) {
            if(c.repeat == 'MONTHLY') self.parseDay(c, day)
            if(c.repeat == 'WEEKLY' && day <= c.day[1] && (self.month + 1) == c.month[1]) self.parseDay(c, day)
            else if(c.repeat == 'WEEKLY' && c.year[0] != c.year[1] && (self.month + 1) < c.month[1]) self.parseDay(c, day)
          }
        }
      } else if(self.year == c.year[0] && (self.month + 1) == c.month[0]) self.parseDay(c, day)
    })
    if(this.curData[day]) return '<div class="fc-calendar-event">' + this.curData[day].html.join('</div><div class="fc-calendar-event">') + '</div>'
    else return ''
  }
   
  Calendario.prototype.parseID = function(data, day) {
    var self = this
    $.each(data, function(i, c) {
      if(!c) {/*ignore*/}
      else self.parseDayID(c, day)
    })
    if(this.curData[day]) return this.curData[day].color
    else return ''
  }

  Calendario.prototype.generateTemplate = function(callback) {
    this.curData = []
    var head     = this.getHead()
    var body     = this.getBody()
    var rowClass = ''

    if(this.rowTotal == 4) rowClass = 'fc-four-rows'
    else if(this.rowTotal == 5) rowClass = 'fc-five-rows'
    else if(this.rowTotal == 6) rowClass = 'fc-six-rows'

    this.$cal = $('<div class="fc-calendar ' + rowClass + '">').append(head, body)
    this.$element.find('div.fc-calendar').remove().end().append(this.$cal)
    this.propDate()
    this.$element.trigger($.Event('shown.calendar.calendario'))
    if(callback) callback.call()
    return true
  }

  Calendario.prototype.getHead = function () {
    var html = '<div class="fc-head row no-gutters align-items-center text-center font-weight-bold">', pos, j
    for(var i = 0; i <= 6; i++) {
      pos = i + this.options.startIn
      j = pos > 6 ? pos - 6 - 1 : pos
      html += '<div class="col">' + (this.options.displayWeekAbbr ? this.options.weekabbrs[j] : this.options.weeks[j]) + '</div>'
    }
    return html + '</div>'
  }

  Calendario.prototype.getBody = function() {
    var d            = new Date(this.year, this.month + 1, 0)
    var monthLength  = d.getDate()
    var firstDay     = new Date(this.year, d.getMonth(), 1)
    var pMonthLength = new Date(this.year, this.month, 0).getDate()
    var html         = '<div class="fc-body"><div class="fc-row row no-gutters align-items-center text-center">'
    var day          = 1
    var startingDay  = firstDay.getDay()
    var pos          = 0
    var p            = 0
    var inner        = ''
    var today        = false
    var past         = false
    var content      = ''
    var idx          = 0
    var data         = ''
    var cellClasses  = ''
    var leagueid     = ''

    for (var i = 0; i < 7; i++) {
      for (var j = 0; j <= 6; j++) {
        pos   = startingDay - this.options.startIn
        p     = pos < 0 ? 6 + pos + 1 : pos
        inner = ''
        today = this.month === this.today.getMonth() && this.year === this.today.getFullYear() && day === this.today.getDate()
        past  = this.year < this.today.getFullYear() || this.month < this.today.getMonth() && this.year === this.today.getFullYear() ||
                this.month === this.today.getMonth() && this.year === this.today.getFullYear() && day < this.today.getDate()
        content = ''
        
		idx     = j + this.options.startIn > 6 ? j + this.options.startIn - 6 - 1 : j + this.options.startIn

        if(this.options.fillEmpty && (j < p || i > 0)) {
          if(day > monthLength) inner = '<span class="fc-emptydate text-gray-400">' + (day++ - monthLength) + '</span><span class="fc-weekday d-none">'
          else if (day == 1) inner = '<span class="fc-emptydate text-gray-400">' + (pMonthLength++ - p + 1) + '</span><span class="fc-weekday d-none">'
          inner += this.options.weekabbrs[idx] + '</span>'
        }
 
       leagueid = ''         
        if (day <= monthLength && (i > 0 || j >= p)) {
          data = Array.prototype.concat(this.caldata[day], this.caldata[this.options.weekabbrs[idx].toUpperCase()])
          .sort(function(a, b){
            return (a.allDay ? '00:00' : a.startTime).replace(':','') - (b.allDay ? '00:00' : b.startTime).replace(':','')
          })
          if(data) content += this.parseDataToDay(data, day)

          if(content) { leagueid = this.parseID(data, day); var lid = leagueid[0].split('|'); }
          inner = '<span id="day-'+day+'" class="fc-date btn-block '+(leagueid !== '' ? 'btn-'+lid[1]+' font-weight-bold text-white' : '')+' text-decoration-none">' + day + '</span><span class="fc-weekday d-none">' + this.options.weekabbrs[idx] + '</span>'
          if(content !== '') inner += '<div class="fc-calendar-events">' + content + '</div>'
          
          ++day;
        } else {
          today = false
        }

        
        cellClasses = (today ? 'fc-today ' : '') + (past ? 'fc-past col border' : 'fc-future col border') 
        html += (cellClasses !== '' ? '<div class="' + cellClasses.trim() + '">' : '<div>') + inner + '</div>'
      }

      if(day > monthLength) {
        this.rowTotal = i + 1
        break
      } else html += '</div><div class="fc-row row no-gutters align-items-center text-center">'
    }
    return html + '</div></div>'
  }

  Calendario.prototype.move = function(period, dir, callback) {
    if(dir === 'previous') {
      if(period === 'month') {
        this.year = this.month > 0 ? this.year : --this.year
        this.month = this.month > 0 ? --this.month : 11
      } else if(period === 'year') this.year = --this.year
    } 
    else if(dir === 'next') {
      if(period === 'month'){
        this.year = this.month < 11 ? this.year : ++this.year
        this.month = this.month < 11 ? ++this.month : 0
      } else if(period === 'year') this.year = ++this.year
    }
    return this.generateTemplate(callback)
  }

  Calendario.prototype.option = function(option, value) {
    if(value) return this.options[option] = value
    else return this.options[option]
  }
  
  Calendario.prototype.getYear = function() {
    return this.year
  }

  Calendario.prototype.getMonth = function() {
    return this.month + 1
  }

  Calendario.prototype.getMonthName = function() {
    return this.options.displayMonthAbbr ? this.options.monthabbrs[this.month] : this.options.months[this.month]
  }
  
  Calendario.prototype.getCell = function(day, data) {
    if (!data) return this.$cal.find("span.fc-date").filter(function(){return $(this).text() == day}).parent()
    else return this.$cal.find("span.fc-date").filter(function(){return $(this).text() == day}).parent().data('bz.calendario.dateprop')
  }

  Calendario.prototype.setData = function(caldata, clear) {
    if(clear) this.caldata = this.processCaldata(caldata)
    else $.extend(this.caldata, this.processCaldata(caldata))
    return this.generateTemplate()
  }

  Calendario.prototype.gotoNow = function(callback) {
    this.month = this.today.getMonth()
    this.year = this.today.getFullYear()
    return this.generateTemplate(callback)
  }

  Calendario.prototype.gotoMonth = function(month, year, callback) {
    this.month = month - 1
    this.year = year
    return this.generateTemplate(callback);
  }

  Calendario.prototype.gotoPreviousMonth = function(callback) {
    return this.move('month', 'previous', callback)
  }

  Calendario.prototype.gotoPreviousYear = function(callback) {
    return this.move('year', 'previous', callback)
  }

  Calendario.prototype.gotoNextMonth = function(callback) {
    return this.move('month', 'next', callback)
  }

  Calendario.prototype.gotoNextYear = function(callback){
    return this.move('year', 'next', callback)
  }

  Calendario.prototype.feed = function() {
    return this.syncData.feed ? this.syncData.feed : 'not-available'
  }

  Calendario.prototype.version = function() {
    return this.INFO.VERSION
  }

  function Plugin(option, value1, value2, value3) {
    var val = ''
    this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bz.calendario')
      var options = typeof option == 'object' && option

      if (!data) $this.data('bz.calendario', (data = new Calendario(this, options)))
      if (typeof option == 'string' && typeof data[option]==="function") return val = data[option](value1, value2, value3)
      else if (typeof option == 'string') return val = data['option'](value1, value2)
    })
    if(val) return val
    else $(document).trigger($.Event('finish.calendar.calendario'))
  }

  var old = $.fn.calendario

  $.fn.calendario             = Plugin
  $.fn.calendario.Constructor = Calendario

  $.fn.calendario.noConflict = function () {
    $.fn.calendario = old
    return this
  }  
}(jQuery);