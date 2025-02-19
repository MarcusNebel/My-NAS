const backgroundImages = [
  'images/image1.jpg',
  'images/image2.jpg',
  'images/image3.jpg',
  'images/image4.jpg'
];

const randomImage = backgroundImages[Math.floor(Math.random() * backgroundImages.length)];

document.body.style.backgroundImage = `url(${randomImage})`;