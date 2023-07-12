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
  


function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.classList.add('alert', `alert-${type}`);
    notification.setAttribute('role', 'alert');
    notification.innerText = message;
    document.body.appendChild(notification);
    notification.style.position = 'fixed'; // добавляем позиционирование
    setTimeout(function() {
      notification.remove();
    }, 3000); // таймаут на 3 секунды
  }
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
        showNotification(data, 'secondary');
        if (data.status === 'success') {
          setTimeout(function() {
            showNotification('Товар успешно добавлен в корзину', 'success');
          }, 3000); // таймаут на 3 секунды
        } else {
          setTimeout(function() {
            showNotification(data.message, 'danger');
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
            showNotification('Количество товара успешно уменьшено', 'success');
          }, 3000); // таймаут на 3 секунды
        } else {
          setTimeout(function() {
            showNotification(data.message, 'danger');
          }, 3000); // таймаут на 3 секунды
        }
      })
      .catch(error => {
        showNotification('Произошла ошибка:', error);
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
            showNotification('Оффер успешно удален из корзины', 'success');
          }, 3000); // таймаут на 3 секунды
        } else {
          setTimeout(function() {
            showNotification(data.message, 'danger');
          }, 3000); // таймаут на 3 секунды
        }
      })
      .catch(error => {
        showNotification('Произошла ошибка:', error);
      });
  }
  