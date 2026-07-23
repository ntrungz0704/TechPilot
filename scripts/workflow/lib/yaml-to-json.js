#!/usr/bin/env node
/**
 * YAML-to-JSON for TechPilot contracts with proper indent-aware tree building.
 */
const fs = require('fs');

function parse(filePath) {
  const content = fs.readFileSync(filePath, 'utf8')
    .replace(/\r\n/g, '\n').replace(/\r/g, '\n');
  const lines = content.split('\n');
  
  // Collect non-blank, non-comment tokens
  const tokens = [];
  for (let i = 0; i < lines.length; i++) {
    const raw = lines[i];
    if (/^\s*$/.test(raw) || /^\s*#/.test(raw)) continue;
    const indent = raw.search(/\S/);
    const rest = raw.slice(indent);
    const isListItem = rest.startsWith('- ');
    const iContent = isListItem ? rest.slice(2) : rest;
    tokens.push({ indent, isListItem, iContent });
  }
  
  // Build tree using indent stack
  const result = [];
  const indentStack = [-1]; // temp
  
  function getParent(indent) {
    // Pop stack until we find a container whose indent < current indent
    while (indentStack.length > 1) {
      if (indent > indentStack[indentStack.length - 1]) break;
      indentStack.pop();
    }
    // Walk from root following the path to current depth
    let parent = result[0];
    for (let i = 1; i < indentStack.length; i++) {
      const entry = parent;
      if (entry && entry.__kids) {
        const kid = entry.__kids[entry.__kids.length - 1];
        if (kid) {
          if (Array.isArray(kid)) parent = kid;
          else if (typeof kid === 'object') parent = kid;
        }
      } else {
        break;
      }
    }
    return parent;
  }
  
  // I'll use a completely different approach - simpler and proven.
  // The real approach: use an explicit node stack.
  return buildWithStack(tokens);
}

function buildWithStack(tokens) {
  // Stack holds references to containers
  // Root is an object
  const root = {};
  const stack = [{ node: root, indent: -1, isArray: false }];
  
  for (let i = 0; i < tokens.length; i++) {
    const tok = tokens[i];
    
    // Pop until stack top indent < current indent
    while (stack.length > 1 && tok.indent <= stack[stack.length - 1].indent) {
      stack.pop();
    }
    
    const top = stack[stack.length - 1];
    
    if (tok.isListItem) {
      // Ensure current container is an array
      if (!top.isArray) {
        // We need to switch... but can't switch top node type.
        // This should only happen when the previous key opened an array container.
        // If not, something is wrong with the contract.
        console.error('ERROR: List item without array container at line (approx)');
        process.exit(1);
      }
      
      const subMatch = tok.iContent.match(/^([A-Za-z_][A-Za-z0-9_]*):\s*(.*)$/);
      if (subMatch) {
        // List of objects: "- key: value"
        const obj = {};
        const key = subMatch[1];
        const val = parseScalar(subMatch[2].trim());
        obj[key] = val;
        top.node.push(obj);
        stack.push({ node: obj, indent: tok.indent, isArray: false });
      } else {
        // Scalar list item
        top.node.push(parseScalar(tok.iContent));
      }
    } else {
      // Key-value pair
      const kvMatch = tok.iContent.match(/^([A-Za-z_][A-Za-z0-9_]*):\s*(.*)$/);
      if (!kvMatch) continue;
      
      const key = kvMatch[1];
      const rawVal = kvMatch[2].trim();
      const val = parseScalar(rawVal);
      
      // Check if next token is deeper = container
      const isContainer = (val === '') && (i + 1 < tokens.length) && 
                         (tokens[i + 1].indent > tok.indent);
      
      if (isContainer) {
        // Determine if array or object by checking next token type
        const nextTok = tokens[i + 1];
        if (nextTok.isListItem) {
          const arr = [];
          top.node[key] = arr;
          stack.push({ node: arr, indent: tok.indent, isArray: true });
        } else {
          const obj = {};
          top.node[key] = obj;
          stack.push({ node: obj, indent: tok.indent, isArray: false });
        }
      } else {
        top.node[key] = val;
      }
    }
  }
  
  return root;
}

function parseScalar(raw) {
  if (raw === '' || raw === undefined) return '';
  if (raw === 'null' || raw === '~') return null;
  if (raw === 'true') return true;
  if (raw === 'false') return false;
  if (/^-?\d+$/.test(raw)) return parseInt(raw, 10);
  if (/^-?\d+\.\d+$/.test(raw)) return parseFloat(raw);
  let v = raw;
  if ((v.startsWith('"') && v.endsWith('"')) || (v.startsWith("'") && v.endsWith("'"))) {
    v = v.slice(1, -1);
  }
  return v;
}

const file = process.argv[2];
if (!file) { console.error('Usage: yaml-to-json.js <file.yaml>'); process.exit(1); }
try {
  const result = parse(file);
  console.log(JSON.stringify(result, null, 2));
} catch (e) {
  console.error('PARSE_ERROR:', e.message);
  process.exit(1);
}
