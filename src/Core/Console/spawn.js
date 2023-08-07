// get argument that container the data from the command
const json = process.argv.slice(2);
// Parse the data
const dataEncoded = JSON.parse(Buffer.from(json[0], "base64").toString("utf-8"));

const { spawn } = require("child_process");
const fs = require("fs");

// Function to start your sub-processes
function startSubProcesses() {
	// Return the sub-processes in case you need to interact with them further
	return dataEncoded.processes.map((script) => spawnProcess(script));
}

// Function to start webpack --watch
function spawnProcess(script) {
	const proc = spawn(script.command, [], {
		// Set the current working directory for the webpack --watch process
		shell: true,
		stdio: "pipe",
	});

	proc.stdout.on("data", (data) => {
		console.log("__________________________________");
		console.log(`${script.name} :`);
		console.log(`${data}`);
		console.log("__________________________________");
	});

	proc.stderr.on("data", (data) => {
		console.error(`Error in ${script.name}: ${data}`);
	});

	proc.on("close", (code) => {
		console.log(`${script.name} process exited with code ${code}`);
	});

	// Handle sub-process exit
	proc.on("exit", (code) =>
		console.log(`${script.name} exited with code ${code}`)
	);

	return proc;
}
// Start the sub-processes when the CLI is launched
const subProcesses = startSubProcesses();

// Listen for the Ctrl+C (SIGINT) signal to stop sub-processes and exit the CLI gracefully
process.on('SIGINT', () => {
  console.log('\nStopping sub-processes...');
  subProcesses.forEach(subProcess => {
    subProcess.kill('SIGINT'); // Send SIGINT signal to each sub-process
  });

  // Optionally, you can perform any cleanup tasks here before exiting
  console.log('Exiting the CLI...');
  process.exit(0); // Exit the process with code 0 (indicating successful termination)
});



