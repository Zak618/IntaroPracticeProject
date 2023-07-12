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
        console.log(data);
        if (data.status === 'success') {
          console.log('Товар успешно добавлен в корзину');
        } else {
          console.log(data.message);
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
          console.log('Количество товара успешно уменьшено');
        } else {
          console.log(data.message);
        }
      })
      .catch(error => {
        console.log('Произошла ошибка:', error);
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
          console.log('Оффер успешно удален из корзины');
        } else {
          console.log(data.message);
        }
      })
      .catch(error => {
        console.log('Произошла ошибка:', error);
      });
  }
  