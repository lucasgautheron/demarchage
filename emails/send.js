const EmailTemplate = require("email-templates");
const path = require("path");
const argv = require('yargs') // eslint-disable-line
  .option('production', {
    alias: 'p',
    type: 'string',
    description: 'Production mode'
  })
  .option('displayName', {
    alias: 'n',
    type: 'string',
    description: 'display name'
  })
  .option('email', {
    alias: 'e',
    type: 'string',
    description: 'user email'
  })
  .option('updateCardUrl', {
    alias: 'u',
    type: 'string',
    description: 'update card url'
  })
  .option('author', {
    alias: 'a',
    type: 'string',
    description: 'user email'
  })
  .option('from', {
    alias: 'f',
    type: 'string',
    description: 'author email'
  })
  .option('template', {
    alias: 't',
    type: 'string',
    description: 'which template to use'
  })
  .option('subject', {
    alias: 's',
    type: 'string',
    description: 'subject'
  })
  .option('attachment', {
    type: 'string',
    description: 'file attachment'
  }).argv


const fs = require('fs');
let mailjet = JSON.parse(fs.readFileSync('mailjet.json'));

MAILJET_USER = mailjet['USER'];
MAILJET_PASS = mailjet['PASS'];

console.log(argv);

production = argv.production === "production";

let emailTemplate = new EmailTemplate({
  views: { root: path.join(__dirname, ".") },
  message: {
    from: '"' + argv.author + ' du Média" <' + argv.from + '>'
  },
  preview: false,
  send: true,
  transport: {
    host: "in-v3.mailjet.com",
    port: 587,
    secure: false, // true for 465, false for other ports
    auth: {
      user: MAILJET_USER,
      pass: MAILJET_PASS
    }
  }
});

console.log(process.env.MAILJET_USER);
console.log(process.env.MAILJET_PASS);

async function __send(template, to, subject, attachment, variables) {
  if (attachment) {
      emailTemplate.attachments = [{
          filename: attachment.filename,
          path: attachment.path
      }];
  }
  await emailTemplate.send({
    template,
    message: {
      to,
      subject
    },
    locals: variables
  })
}

async function sendEmail(template, subject, user, author, customText = "", attachment = null) {
  __send(
    template,
    user.email,
    subject,
    attachment,
    {
      title: subject,
      name: user.displayName,
      signature: author.displayName,
      email: user.email,
      link: user.updateCardUrl,
      customText: customText
    }
  );
  return true;
}


user = {
    displayName: argv.displayName,
    updateCardUrl: argv.updateCardUrl,
    email: (production ? argv.email : "lucas.gautheron@gmail.com")
};

author = {
    displayName: argv.author,
    email: argv.from,
};

let attachment = null;
if (argv.attachment)
{
    attachment = {
        filename: argv.attachment;
        path: "attachments/" + argv.attachment;
    };
}

sendEmail(argv.template, argv.subject, user, author, "", attachment);


