name: CI/CD Pipeline

on:
  push:
    branches: ["main"]

jobs:
  build:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Setup Java
        uses: actions/setup-java@v3
        with:
          distribution: 'temurin'
          java-version: '17'

      - name: Build
        run: mvn clean install

      - name: Test
        run: mvn test

      - name: Deploy
        run: echo "Deploy successful!"
