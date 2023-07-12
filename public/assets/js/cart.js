// function addToCart(offerId) {
//     fetch(`/product/${offerId}`, {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//       },
//       body: JSON.stringify({ offerid: offerId }),
//     })
//       .then(response => response.json())
//       .then(data => {
//         alert(data);
//         if (data.status === 'success') {
//           alert('Товар успешно добавлен в корзину');
//         } else {
//             alert(data.message);
//         }
//       })
//       .catch(error => {
//         console.log('Произошла ошибка:', error);
//       });
//   }
  
//   function decreaseQuantity(offerId) {
//     fetch(`/product/${offerId}`, {
//       method: 'DELETE',
//       headers: {
//         'Content-Type': 'application/json',
//       },
//       body: JSON.stringify({ offerid: offerId }),
//     })
//       .then(response => response.json())
//       .then(data => {
//         if (data.status === 'success') {
//           // TODO нужно показывать https://getbootstrap.ru/docs/5.1/components/alerts/
//           // красивые уведомленьки внизу где-нибудь
//           // с таймером, чтобы потом они пропадали
//             alert('Количество товара успешно уменьшено');
//         } else {
//             alert(data.message);
//         }
//       })
//       .catch(error => {
//         alert('Произошла ошибка:', error);
//       });
//   }
  
//   function removeFromCart(offerId) {
//     fetch(`/product/${offerId}/all`, {
//       method: 'DELETE',
//       headers: {
//         'Content-Type': 'application/json',
//       },
//       body: JSON.stringify({ offerid: offerId }),
//     })
//       .then(response => response.json())
//       .then(data => {
//         console.log(data);
//         if (data.status === 'success') {
//             alert('Оффер успешно удален из корзины');
//         } else {
//             alert(data.message);
//         }
//       })
//       .catch(error => {
//         alert('Произошла ошибка:', error);
//       });
//   }
  


function addToCart(offerId) {
    fetch(`/product/${offerId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ offerid: offerId }),
    })
      .then(response => response.json())
      .then(data => {
        alert(data);
        if (data.status === 'success') {
          setTimeout(function() {
            alert('Товар успешно добавлен в корзину');
          }, 3000); // таймаут на 3 секунды
        } else {
          setTimeout(function() {
            alert(data.message);
          }, 3000); // таймаут на 3 секунды
        }
      })
      .catch(error => {
        console.log('Произошла ошибка:', error);
      });
  }
  function decreaseQuantity(offerId) {
    fetch(`/product/${offerId}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ offerid: offerId }),
    })
      .then(response => response.json())
      .then(data => {
        console.log(data);
        if (data.status === 'success') {
          setTimeout(function() {
            alert('Количество товара успешно уменьшено');
          }, 3000); // таймаут на 3 секунды
        } else {
          setTimeout(function() {
            alert(data.message);
          }, 3000); // таймаут на 3 секунды
        }
      })
      .catch(error => {
        alert('Произошла ошибка:', error);
      });
  }
  function removeFromCart(offerId) {
    fetch(`/product/${offerId}/all`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ offerid: offerId }),
    })
      .then(response => response.json())
      .then(data => {
        console.log(data);
        if (data.status === 'success') {
          setTimeout(function() {
            alert('Оффер успешно удален из корзины');
          }, 3000); // таймаут на 3 секунды
        } else {
          setTimeout(function() {
            alert(data.message);
          }, 3000); // таймаут на 3 секунды
        }
      })
      .catch(error => {
        alert('Произошла ошибка:', error);
      });
  }
  