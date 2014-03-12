var App = App || {
  // 皇居
  coords: {
    lat: 35.686515,
    lng: 139.751594
  }
};


/*

// http://en.wikipedia.org/wiki/Isbn#Check_digits
function isValidISBN10(ISBNumber){
  var isbn = ISBNumber.replace(/[^0-9x]/g, '');
  var isbn_check = isbn.substr(0, isbn.length-1);
  var sum = 0;
  if (isbn.length==10){
    for(var i = 2; i<11; i++){
      sum = sum + (isbn.substr(10-i,1)*i)
    }
    var CheckDigit = 11-(sum%11);
    if (CheckDigit == 10){
      CheckDigit = 'x';
    }
    if (isbn.substr(isbn.length-1, 1).toLowerCase()==CheckDigit){
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}


// http://en.wikipedia.org/wiki/Isbn#Check_digits
function isValidISBN13(ISBNumber) {
  var sum, check, i;

  ISBNumber = ISBNumber.replace(/[^\d]/g,'');

  sum = 0;
  for (i = 0; i < 12; i += 1) {
    // checks for odd/even position to multiply by 1 or 3
    // uses Javascript implicit conversion of _number string_
    // placing unary operator "+a", better than call function parseInt(a, 10);
    sum += +ISBNumber.charAt(i) * (i % 2 ? 1 : 3);
  }
  check = sum % 10;
  return (+ISBNumber.charAt(12) === check);
}

*/


function search(query) {
  return $.ajax({
    url: 'calilRelaition.php',
    dataType: 'json',
    data: query
  });
}


function display(data) {
  var source = $('#results-item-template').html();
  var html = _.template(source, data);

  $('#search-results').empty().html(html);
}


function geolocation() {
  var deferred = $.Deferred();

  // http://www.htmq.com/geolocation/
  if (!navigator.geolocation) {
    console.log('ok');

    navigator.geolocation.getCurrentPosition(function(position) {
      var lat = position.coords.latitude;
      var lng = position.coords.longitude;
      var coords = { lat: lat, lng: lng };

      console.log('success', position);
      console.log('緯度', lat);
      console.log('経度', lng);

      deferred.resolve(coords);

    }, function(error) {
      console.log('error', error);

      switch(error.code) {
        case 1:
          // 位置情報の利用が許可されていません
          break;
        case 2:
          // デバイスの位置が判定できません
          break;
        case 3:
          // タイムアウト
          break;
      }

      deferred.reject();
    });

  } else {
    console.log('bad');

    deferred.reject();
  }

  return deferred.promise();
}


function onKeyup(ev) {
  var value = $(this).val();

  /*
  console.log('本の名前, ISBN', value);
  var test = {
    "book": { "title": "本のタイトル", "isbn": "1234567890123" },
    "library": [
      { "name": "図書館名1", "address": "住所1", "tel": "03-1234-5678" },
      { "name": "図書館名2", "address": "住所2", "tel": "03-1234-5678" },
      { "name": "図書館名3", "address": "住所3", "tel": "03-1234-5678" }
    ]
  };
  display(test);
  */

  search({
    lat: App.coords.lat,
    lon: App.coords.lng,
    name: value,
    isbn: ''
  })
    .done(function(results) {
      console.log(results);

      display(results);
    })
    .fail(function() {
      // TODO:
      alert('結果の取得に失敗しました');
    });

  // disable enter key
  if ((ev.which && ev.which === 13) || (ev.keyCode && ev.keyCode === 13)) {
    return false;
  } else {
    return true;
  }
}


$(function() {

  // 検索入力フィールド
  var $search = $('#search-input input[type="search"]');

  geolocation()
    .done(function(data) {
      console.log(coords);

      App.coords = coords;
    })
    .fail(function() {
    })
    .always(function() {
      $search.removeClass('hide');
    });
    
  $search.on('keyup', onKeyup);

});

