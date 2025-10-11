console.log(KhadafiShop.halaman()[0]);
// Stack Teknologi Website Toko Online

const teknologi = {
  frontend: ["HTML", "CSS", "JavaScript"],

  backend: ["PHP", "Node.js"],

  database: "MySQL",

  framework_tools: ["Bootstrap", "Express.js", "VS Code"],
};

console.log("Stack Teknologi:", teknologi);

class WebsiteTokoOnline extends Website {
  static nameApp = "Khadafi Shop";
  static author = "Rafa Khadafi";
  static version = "1.0.0";
  static category = "E-Commerce Website";
  static description =
    "Platform toko online sederhana untuk belanja cepat & praktis.";
  static dateCreated = "September 2025";
}

// test