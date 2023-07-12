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
//         console.log(data);
//         if (data.status === 'success') {
//           console.log('Товар успешно добавлен в корзину');
//         } else {
//           console.log(data.message);
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
//         console.log(data);
//         if (data.status === 'success') {
//           console.log('Количество товара успешно уменьшено');
//         } else {
//           console.log(data.message);
//         }
//       })
//       .catch(error => {
//         console.log('Произошла ошибка:', error);
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
//           console.log('Оффер успешно удален из корзины');
//         } else {
//           console.log(data.message);
//         }
//       })
//       .catch(error => {
//         console.log('Произошла ошибка:', error);
//       });
//   }
  



async function changeCart(offer_id) {
    try {
       const response = await fetch(`/product/${offer_id}`, {
          method: 'POST',
          headers: {
             'Content-Type': 'application/json'
          },
          body: JSON.stringify({
             offer_id: offer_id
          })
       });
       const data = await response.json();
       if (data.status === 'success') {
          alert('Cart updated successfully');
       } else {
          alert('Failed to update cart');
       }
    } catch (error) {
       alert('Error:', error);
    }
 }
 
 async function decreaseQuantity(offer_id) {
    try {
       const response = await fetch(`/product/${offer_id}`, {
          method: 'DELETE',
          headers: {
             'Content-Type': 'application/json'
          },
          body: JSON.stringify({
             offer_id: offer_id
          })
       });
       const data = await response.json();
       if (data.status === 'success') {
          alert('Quantity decreased successfully');
       } else {
          alert('Failed to decrease quantity');
       }
    } catch (error) {
       alert('Error:', error);
    }
 }
 
 async function removeOffer(offer_id) {
    try {
       const response = await fetch(`/product/${offer_id}/all`, {
          method: 'DELETE',
          headers: {
             'Content-Type': 'application/json'
          },
          body: JSON.stringify({
             offer_id: offer_id
          })
       });
       const data = await response.json();
       if (data.status === 'success') {
          alert('Offer removed successfully');
       } else {
          alert('Failed to remove offer');
       }
    } catch (error) {
       alert('Error:', error);
    }
 }



 // Получаем все элементы с классом "change-cart-btn"
const changeCartBtns = document.getElementsByClassName("change-cart-btn");
Array.from(changeCartBtns).forEach(btn => {
   btn.addEventListener("click", async () => {
      const offer_id = btn.dataset.offerId;
      await changeCart(offer_id);
   });
});
// Получаем все элементы с классом "decrease-quantity-btn"
const decreaseQuantityBtns = document.getElementsByClassName("decrease-quantity-btn");
Array.from(decreaseQuantityBtns).forEach(btn => {
   btn.addEventListener("click", async () => {
      const offer_id = btn.dataset.offerId;
      await decreaseQuantity(offer_id);
   });
});
// Получаем все элементы с классом "remove-offer-btn"
const removeOfferBtns = document.getElementsByClassName("remove-offer-btn");
Array.from(removeOfferBtns).forEach(btn => {
   btn.addEventListener("click", async () => {
      const offer_id = btn.dataset.offerId;
      await removeOffer(offer_id);
   });
});
