document.addEventListener("DOMContentLoaded", () => {
  const menu = document.querySelector(".menu");
  const nav = document.querySelector("nav");
  const faBars = document.querySelector("nav .menu .fa-bars");
  const faX = document.querySelector("nav .menu .fa-x");
  const navLinks = document.querySelectorAll(".nav a");
  const navElement = document.querySelector(".nav");
  const textElement = document.getElementById("typing-text");

  // Menu click event
  menu.addEventListener("click", () => {
    nav.classList.toggle("active");
    faBars.classList.toggle("active");
    faX.classList.toggle("active");
  });

  // Window scroll event
  window.onscroll = () => {
    nav.classList.remove("active");
    faX.classList.remove("active");
    faBars.classList.add("active");
  };

  // Shrink nav on scroll
  window.addEventListener("scroll", () => {
    if (window.scrollY > 0) {
      navElement.classList.add("shrink");
    } else {
      navElement.classList.remove("shrink");
    }
  });

  // Nav link click event
  navLinks.forEach(link => {
    link.addEventListener("click", () => {
      faX.classList.remove("active");
      faBars.classList.add("active");
      nav.classList.remove("active");
    });
  });

  // Typing effect
  const words = document.title
    ? ['do Front-End Development.', 'do Back-end Development.', 'Design Website.', 'Manage Database.']
    : ['Faire du développement Front-End.', 'Faire du développement Back-End.', 'Concevoir un site Web.', 'Gérer une base de données.'];
  let wordIndex = 0;
  let charIndex = 0;

  function type() {
    if (charIndex < words[wordIndex].length) {
      textElement.textContent += words[wordIndex].charAt(charIndex);
      charIndex++;
      setTimeout(type, 100);
    } else {
      setTimeout(erase, 1000);
    }
  }

  function erase() {
    if (charIndex > 0) {
      textElement.textContent = words[wordIndex].substring(0, charIndex - 1);
      charIndex--;
      setTimeout(erase, 100);
    } else {
      wordIndex = (wordIndex + 1) % words.length;
      setTimeout(type, 500);
    }
  }

  setTimeout(type, 500);
});
