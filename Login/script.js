const backgroundImages = [
  'images/image1.jpg',
  'images/image2.jpg',
  'images/image3.jpg',
  'images/image4.jpg',
  'images/image5.jpg',
  'images/image6.jpg',
  'images/image7.jpg',
  'images/image8.jpg'
];

const randomImage = backgroundImages[Math.floor(Math.random() * backgroundImages.length)];

document.body.style.backgroundImage = `url(${randomImage})`;