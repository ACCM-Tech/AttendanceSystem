const numbers = "123456789";

const generate4DigitNumber = () => {
  let number = "";
  for (let i = 0; i < 4; i++) {
    number += numbers[Math.floor(Math.random() * numbers.length)];
  }
  return number;
};

const generateUnique4DigitNumbers = (count) => {
  const uniqueNumbers = new Set();
  
  while (uniqueNumbers.size < count) {
    const newNumber = generate4DigitNumber();
    uniqueNumbers.add(newNumber);
  }
  
  return Array.from(uniqueNumbers);
};

const unique4DigitNumbers = generateUnique4DigitNumbers(800);

const saveTheGeneratedNumbersAsTXT = () => {
  const fs = require("fs");
  const filePath = "unique4DigitNumbers.txt";
  
  fs.writeFile(filePath, unique4DigitNumbers.join("\n"), (err) => {
    if (err) {
      console.error("Error writing to file:", err);
    } else {
      console.log("Numbers saved to file:", filePath);
    }
  });
  return unique4DigitNumbers;
  };
  
saveTheGeneratedNumbersAsTXT();