#!/usr/bin/env node
/**
 * load-contract.js — Canonical contract parser and validator
 *
 * Single shared module for YAML parsing (js-yaml) and
 * JSON Schema Draft-07 validation (ajv).
 *
 * Every workflow script MUST use this module rather than
 * implementing its own YAML/schema logic.
 */
const fs = require('fs');
const path = require('path');
const yaml = require('js-yaml');
const Ajv = require('ajv');
const addFormats = require('ajv-formats');

// ── Public API ──────────────────────────────────────────────────────────────

function loadContract(filePath) {
  const raw = fs.readFileSync(filePath, 'utf8');
  const obj = yaml.load(raw);
  if (!obj || typeof obj !== 'object' || Array.isArray(obj)) {
    throw new Error('Contract must be a non-null plain object');
  }
  return obj;
}

function validateContractSchema(obj, schemaPath) {
  const schema = JSON.parse(fs.readFileSync(schemaPath, 'utf8'));
  const ajv = new Ajv({ strict: false });
  addFormats(ajv);
  const validate = ajv.compile(schema);
  if (!validate(obj)) {
    const msgs = validate.errors.map(function(e) {
      return (e.instancePath || '/') + ' ' + e.message + (e.params ? ' (' + JSON.stringify(e.params) + ')' : '');
    });
    throw new Error('Schema validation failed:\n  - ' + msgs.join('\n  - '));
  }
  return true;
}

function loadAndValidate(filePath, schemaPath) {
  const obj = loadContract(filePath);
  validateContractSchema(obj, schemaPath);
  return obj;
}

// ── Accessors ───────────────────────────────────────────────────────────────

function getAllowedPaths(obj) {
  return Array.isArray(obj.ALLOWED_PATHS) ? obj.ALLOWED_PATHS : [];
}

function getForbiddenPaths(obj) {
  return Array.isArray(obj.FORBIDDEN_PATHS) ? obj.FORBIDDEN_PATHS : [];
}

function getRequiredTests(obj) {
  return Array.isArray(obj.REQUIRED_TESTS) ? obj.REQUIRED_TESTS : [];
}

function getRequiredEvidence(obj) {
  var ev = obj.REQUIRED_EVIDENCE;
  if (!Array.isArray(ev)) return [];
  // Normalize: if strings, return as-is; if objects with id+path, return paths
  return ev.map(function(e) {
    if (typeof e === 'string') return e;
    if (e && typeof e === 'object' && e.path) return e.path;
    return JSON.stringify(e);
  });
}

function getRequiredEvidenceEntries(obj) {
  var ev = obj.REQUIRED_EVIDENCE;
  if (!Array.isArray(ev)) return [];
  return ev.filter(function(e) {
    return e && typeof e === 'object' && e.id && e.path;
  });
}

function getLifecycleStatus(obj) {
  return obj.LIFECYCLE_STATUS || null;
}

function getCheckpointId(obj) {
  return obj.CHECKPOINT_ID || null;
}

function dumpAccessor(accessorName, filePath, schemaPath) {
  const obj = schemaPath ? loadAndValidate(filePath, schemaPath) : loadContract(filePath);
  var result;
  switch (accessorName) {
    case 'allowed-paths':
      result = getAllowedPaths(obj);
      break;
    case 'forbidden-paths':
      result = getForbiddenPaths(obj);
      break;
    case 'required-tests':
      result = getRequiredTests(obj);
      break;
    case 'required-evidence':
      result = getRequiredEvidence(obj);
      break;
    case 'lifecycle':
      result = getLifecycleStatus(obj);
      break;
    case 'checkpoint-id':
      result = getCheckpointId(obj);
      break;
    default:
      throw new Error('Unknown accessor: ' + accessorName);
  }
  console.log(JSON.stringify(result));
}

// ── CLI dispatch ────────────────────────────────────────────────────────────

if (require.main === module) {
  var cmd = process.argv[2];
  var arg1 = process.argv[3];
  var arg2 = process.argv[4];

  if (!cmd || cmd === '-h' || cmd === '--help') {
    console.error('Usage:');
    console.error('  load-contract.js validate <contract.yaml> <schema.json>');
    console.error('  load-contract.js dump-allowed-paths <contract.yaml>');
    console.error('  load-contract.js dump-forbidden-paths <contract.yaml>');
    console.error('  load-contract.js dump-required-tests <contract.yaml>');
    console.error('  load-contract.js dump-required-evidence <contract.yaml>');
    console.error('  load-contract.js dump-lifecycle <contract.yaml>');
    console.error('  load-contract.js dump-checkpoint-id <contract.yaml>');
    process.exit(cmd ? 1 : 0);
  }

  try {
    switch (cmd) {
      case 'validate':
        if (!arg1 || !arg2) throw new Error('Usage: validate <contract.yaml> <schema.json>');
        loadAndValidate(arg1, arg2);
        console.log('PASS: Schema validation passed');
        break;
      case 'dump-allowed-paths':
      case 'dump-forbidden-paths':
      case 'dump-required-tests':
      case 'dump-required-evidence':
      case 'dump-lifecycle':
      case 'dump-checkpoint-id':
        if (!arg1) throw new Error('Usage: ' + cmd + ' <contract.yaml>');
        dumpAccessor(cmd.replace(/^dump-/, ''), arg1, arg2);
        break;
      default:
        throw new Error('Unknown command: ' + cmd);
    }
  } catch (e) {
    console.error('FAIL: ' + e.message);
    process.exit(1);
  }
}

module.exports = {
  loadContract: loadContract,
  validateContractSchema: validateContractSchema,
  loadAndValidate: loadAndValidate,
  getAllowedPaths: getAllowedPaths,
  getForbiddenPaths: getForbiddenPaths,
  getRequiredTests: getRequiredTests,
  getRequiredEvidence: getRequiredEvidence,
  getRequiredEvidenceEntries: getRequiredEvidenceEntries,
  getLifecycleStatus: getLifecycleStatus,
  getCheckpointId: getCheckpointId
};
