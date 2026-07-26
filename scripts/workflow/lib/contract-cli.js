#!/usr/bin/env node
/**
 * contract-cli.js — Stable shell-callable interface for load-contract.js
 *
 * All workflow shell scripts use this CLI rather than calling
 * load-contract.js directly. This provides a consistent, versioned
 * command interface.
 *
 * Imports from ./load-contract.js — no direct require of js-yaml or ajv.
 *
 * Commands:
 *   validate <contract.yaml> <schema.json>
 *   validate-json <json-file> <schema.json>
 *   dump-allowed-paths <contract.yaml>
 *   dump-forbidden-paths <contract.yaml>
 *   dump-required-tests <contract.yaml>
 *   dump-required-evidence <contract.yaml>
 *   dump-lifecycle <contract.yaml>
 *   dump-checkpoint-id <contract.yaml>
 */
var path = require('path');
var loader = require('./load-contract');

var cmd = process.argv[2];
var arg1 = process.argv[3];
var arg2 = process.argv[4];

if (!cmd || cmd === '-h' || cmd === '--help') {
  console.error('Usage: contract-cli.js <command> [args...]');
  console.error('Commands:');
  console.error('  validate <contract.yaml> <schema.json>');
  console.error('  validate-json <json-file> <schema.json>');
  console.error('  dump-allowed-paths <contract.yaml> <schema.json>');
  console.error('  dump-forbidden-paths <contract.yaml> <schema.json>');
  console.error('  dump-required-tests <contract.yaml> <schema.json>');
  console.error('  dump-required-evidence <contract.yaml> <schema.json>');
  console.error('  dump-required-evidence-entries <contract.yaml> <schema.json>');
  console.error('  dump-lifecycle <contract.yaml> <schema.json>');
  console.error('  dump-checkpoint-id <contract.yaml> <schema.json>');
  process.exit(cmd ? 1 : 0);
}

try {
  switch (cmd) {
    case 'validate':
      if (!arg1 || !arg2) throw new Error('Usage: validate <contract.yaml> <schema.json>');
      loader.loadAndValidate(arg1, arg2);
      console.log('PASS: Schema validation passed');
      break;

    case 'validate-json':
      if (!arg1 || !arg2) throw new Error('Usage: validate-json <json-file> <schema.json>');
      var obj = JSON.parse(require('fs').readFileSync(arg1, 'utf8'));
      loader.validateContractSchema(obj, arg2);
      console.log('PASS: JSON schema validation passed');
      break;

    case 'dump-allowed-paths':
      if (!arg1 || !arg2) throw new Error('Usage: dump-allowed-paths <contract.yaml> <schema.json>');
      console.log(JSON.stringify(loader.getAllowedPaths(loader.loadAndValidate(arg1, arg2))));
      break;

    case 'dump-forbidden-paths':
      if (!arg1 || !arg2) throw new Error('Usage: dump-forbidden-paths <contract.yaml> <schema.json>');
      console.log(JSON.stringify(loader.getForbiddenPaths(loader.loadAndValidate(arg1, arg2))));
      break;

    case 'dump-required-tests':
      if (!arg1 || !arg2) throw new Error('Usage: dump-required-tests <contract.yaml> <schema.json>');
      console.log(JSON.stringify(loader.getRequiredTests(loader.loadAndValidate(arg1, arg2))));
      break;

    case 'dump-required-evidence':
      if (!arg1 || !arg2) throw new Error('Usage: dump-required-evidence <contract.yaml> <schema.json>');
      console.log(JSON.stringify(loader.getRequiredEvidence(loader.loadAndValidate(arg1, arg2))));
      break;

    case 'dump-required-evidence-entries':
      if (!arg1 || !arg2) throw new Error('Usage: dump-required-evidence-entries <contract.yaml> <schema.json>');
      console.log(JSON.stringify(loader.getRequiredEvidenceEntries(loader.loadAndValidate(arg1, arg2))));
      break;

    case 'dump-lifecycle':
      if (!arg1 || !arg2) throw new Error('Usage: dump-lifecycle <contract.yaml> <schema.json>');
      console.log(loader.getLifecycleStatus(loader.loadAndValidate(arg1, arg2)) || '');
      break;

    case 'dump-checkpoint-id':
      if (!arg1 || !arg2) throw new Error('Usage: dump-checkpoint-id <contract.yaml> <schema.json>');
      console.log(loader.getCheckpointId(loader.loadAndValidate(arg1, arg2)) || '');
      break;

    default:
      throw new Error('Unknown command: ' + cmd);
  }
} catch (e) {
  console.error('FAIL: ' + e.message);
  process.exit(1);
}
