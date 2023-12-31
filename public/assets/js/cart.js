function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.classList.add('alert', `alert-${type}`, 'notification');
    notification.setAttribute('role', 'alert');
    notification.innerText = message;
    document.body.appendChild(notification);
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
  