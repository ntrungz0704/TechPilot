#!/usr/bin/env node
/**
 * path-policy.js — Canonical path pattern matching for workflow policy
 *
 * Exports:
 *   globToRegex(pattern)     - convert glob to RegExp object
 *   matchesPattern(file, pattern)  - true if file matches the glob pattern
 *   matchesAny(file, patterns)     - true if file matches any pattern in array
 *   assertSubset(candidate, canonical) - exits 1 if candidate not subset of canonical
 *
 * Pattern rules:
 *   *        -> matches within one path segment (no /)
 *   **       -> matches multiple segments, can match zero segments
 *   ?        -> matches exactly one character except /
 *   /** at end -> matches the directory itself AND everything under it
 *   All regex metacharacters are escaped.
 */

function normalizePath(path) {
  if (typeof path !== 'string') {
    return path;
  }
  var normalized = path.replace(/\\/g, '/');
  var segments = normalized.split('/');
  for (var i = 0; i < segments.length; i++) {
    if (segments[i] === '..') {
      throw new Error('Path traversal not allowed: ' + path);
    }
  }
  return normalized;
}

function globToRegex(pattern) {
  if (typeof pattern !== 'string' || pattern.length === 0) {
    throw new Error('Invalid pattern: must be a non-empty string');
  }

  var p = normalizePath(pattern);

  var parts = [];
  var i = 0;
  var len = p.length;

  while (i < len) {
    var c = p[i];

    if (c === '*') {
      if (i + 1 < len && p[i + 1] === '*') {
        // ** detected
        i += 2;
        if (i < len && p[i] === '/') {
          // **/ -> (.*/)?
          parts.push('(.*/)?');
          i++;
        } else if (i >= len) {
          // ** at end
          if (parts.length > 0 && parts[parts.length - 1] === '/') {
            parts.pop();
            parts.push('(/.*)?');
          } else {
            parts.push('.*');
          }
        } else {
          // ** in middle not followed by /
          parts.push('.*');
        }
      } else {
        // Single *
        parts.push('[^/]*');
        i++;
      }
    } else if (c === '?') {
      parts.push('[^/]');
      i++;
    } else if (c === '/') {
      parts.push('/');
      i++;
    } else {
      // Escape regex metacharacters: \ . + * ? ( ) [ ] { } | ^ $
      if ('\\.[]{}()*+?|^$'.indexOf(c) >= 0) {
        parts.push('\\' + c);
      } else {
        parts.push(c);
      }
      i++;
    }
  }

  return new RegExp('^' + parts.join('') + '$');
}

function matchesPattern(file, pattern) {
  if (typeof file !== 'string' || typeof pattern !== 'string') return false;
  var normFile = normalizePath(file);
  var normPattern = normalizePath(pattern);
  var re = globToRegex(normPattern);
  return re.test(normFile);
}

function matchesAny(file, patterns) {
  if (!Array.isArray(patterns)) return false;
  for (var i = 0; i < patterns.length; i++) {
    if (matchesPattern(file, patterns[i])) return true;
  }
  return false;
}

function assertSubset(candidatePatterns, canonicalPatterns) {
  if (!Array.isArray(candidatePatterns) || !Array.isArray(canonicalPatterns)) {
    console.error('FAIL: assertSubset requires two arrays');
    process.exit(1);
  }
  var errors = [];
  for (var i = 0; i < candidatePatterns.length; i++) {
    var cp = candidatePatterns[i];
    var found = false;
    for (var j = 0; j < canonicalPatterns.length; j++) {
      if (matchesPattern(cp, canonicalPatterns[j])) {
        found = true;
        break;
      }
    }
    if (!found) {
      errors.push('FAIL: Pattern "' + cp + '" is not a subset of canonical allowlist');
    }
  }
  if (errors.length > 0) {
    errors.forEach(function(e) { console.error(e); });
    process.exit(1);
  }
}

// ── CLI mode for bash callers ───────────────────────────────────────────────
if (require.main === module) {
  var cmd = process.argv[2];
  var arg1 = process.argv[3];
  var arg2 = process.argv[4];

  switch (cmd) {
    case 'match':
      process.exit(matchesPattern(arg1, arg2) ? 0 : 1);
      break;
    case 'test':
      try { var patterns = JSON.parse(arg2); } catch(e) { console.error('FAIL: Invalid patterns JSON'); process.exit(1); }
      process.exit(matchesAny(arg1, patterns) ? 0 : 1);
      break;
    case 'validate':
      try { var cand = JSON.parse(arg1); var canon = JSON.parse(arg2); } catch(e) { console.error('FAIL: Invalid JSON'); process.exit(1); }
      assertSubset(cand, canon);
      console.log('OK: All patterns are within canonical allowlist');
      break;
    default:
      console.error('Usage: path-policy.js match <file> <pattern>');
      console.error('       path-policy.js test <file> <patterns-json>');
      console.error('       path-policy.js validate <patterns-json> <canonical-json>');
      process.exit(1);
  }
}

module.exports = {
  normalizePath: normalizePath,
  matchesPattern: matchesPattern,
  matchesAny: matchesAny,
  assertSubset: assertSubset,
  globToRegex: globToRegex
};
