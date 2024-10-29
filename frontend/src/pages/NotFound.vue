<template>
    <div class="not-found-container">
      <h1>404 - Page Not Found</h1>
      <p>The page you are looking for doesn't exist.</p>
      <router-link to="/">Go to Dashboard</router-link>
    </div>
  </template>
  
  <script>
  export default {
    name: 'NotFound',
  };
  </script>
  
  <style scoped>
  .not-found-container {
    text-align: center;
    padding: 5rem;
  }
  
  .not-found-container h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
  }
  
  .not-found-container p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
  }
  
  .not-found-container a {
    color: #42b983;
    text-decoration: none;
    font-weight: bold;
  }
  
  .not-found-container a:hover {
    text-decoration: underline;
  }
  </style>
  