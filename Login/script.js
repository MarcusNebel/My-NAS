const backgroundImages = [
  '.nas-website/Login/images/image1.jpg',
  '.nas-website/Login/images/image2.jpg',
  '.nas-website/Login/images/image3.jpg',
  '.nas-website/Login/images/image4.jpg',
  '.nas-website/Login/images/image5.jpg',
  '.nas-website/Login/images/image6.jpg',
  '.nas-website/Login/images/image7.jpg',
  '.nas-website/Login/images/image8.jpg',
  '.nas-website/Login/images/image9.jpg',
  '.nas-website/Login/images/image10.jpg',
  '.nas-website/Login/images/image11.jpg',
  '.nas-website/Login/images/image12.jpg',
  '.nas-website/Login/images/image13.jpg',
  '.nas-website/Login/images/image14.jpg',
  '.nas-website/Login/images/image15.jpg',
  '.nas-website/Login/images/image16.jpg',
  '.nas-website/Login/images/image17.jpg',
  '.nas-website/Login/images/image18.jpg',
  '.nas-website/Login/images/image19.jpg'
];

const randomImage = backgroundImages[Math.floor(Math.random() * backgroundImages.length)];

document.body.style.backgroundImage = `url(${randomImage})`;