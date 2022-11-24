#!/bin/sh

# Note that this does not use pipefail
# because if the grep later doesn't match any deleted files,
# which is likely the majority case,
# it does not exit with a 0, and we only care about the final exit.
set -eo

# Allow some ENV variables to be customized
if [[ -z "$SLUG" ]]; then
	SLUG=${GITHUB_REPOSITORY#*/}
fi
echo "ℹ︎ SLUG is $SLUG"

if [[ -z "$BUILD_DIR" ]] || [[ $BUILD_DIR == "./" ]]; then
	BUILD_DIR=false
elif [[ $BUILD_DIR == ./* ]]; then
	BUILD_DIR=${BUILD_DIR:2}
fi

if [[ "$BUILD_DIR" != false ]]; then
	if [[ $BUILD_DIR != /* ]]; then
		BUILD_DIR="${GITHUB_WORKSPACE%/}/${BUILD_DIR%/}"
	fi
	echo "ℹ︎ BUILD_DIR is $BUILD_DIR"
fi

SVN_URL="https://plugins.svn.wordpress.org/${SLUG}/"
SVN_DIR="${HOME}/svn-${SLUG}"

# Checkout just trunk for efficiency
# Tagging will be handled on the SVN level
echo "➤ Checking out .org repository..."
svn checkout --depth immediates "$SVN_URL" "$SVN_DIR"
cd "$SVN_DIR"
svn update --set-depth infinity trunk

if [[ "$BUILD_DIR" = false ]]; then
	echo "➤ Copying files..."
	if [[ -e "$GITHUB_WORKSPACE/.distignore" ]]; then
		echo "ℹ︎ Using .distignore"
		# Copy from current branch to /trunk, excluding dotorg assets
		# The --delete flag will delete anything in destination that no longer exists in source
		rsync -rc --exclude-from="$GITHUB_WORKSPACE/.distignore" "$GITHUB_WORKSPACE/" trunk/ --delete --delete-excluded
	else
		echo "ℹ︎ Using .gitattributes"

		cd "$GITHUB_WORKSPACE"

		# "Export" a cleaned copy to a temp directory
		TMP_DIR="${HOME}/archivetmp"
		mkdir "$TMP_DIR"

		git config --global user.email "10upbot+github@10up.com"
		git config --global user.name "10upbot on GitHub"

		# If there's no .gitattributes file, write a default one into place
		if [[ ! -e "$GITHUB_WORKSPACE/.gitattributes" ]]; then
			cat > "$GITHUB_WORKSPACE/.gitattributes" <<-EOL
			/.gitattributes export-ignore
			/.gitignore export-ignore
			/.github export-ignore
			EOL

			# Ensure we are in the $GITHUB_WORKSPACE directory, just in case
			# The .gitattributes file has to be committed to be used
			# Just don't push it to the origin repo :)
			git add .gitattributes && git commit -m "Add .gitattributes file"
		fi

		# This will exclude everything in the .gitattributes file with the export-ignore flag
		git archive HEAD | tar x --directory="$TMP_DIR"

		cd "$SVN_DIR"

		# Copy from clean copy to /trunk, excluding dotorg assets
		# The --delete flag will delete anything in destination that no longer exists in source
		rsync -rc "$TMP_DIR/" trunk/ --delete --delete-excluded
	fi
else
	echo "ℹ︎ Copying files from build directory..."
	rsync -rc "$BUILD_DIR/" trunk/ --delete --delete-excluded
fi

echo "➤ Generating zip file..."
cd "$SVN_DIR/trunk" || exit
zip -r "${GITHUB_WORKSPACE}/${SLUG}.zip" .
echo "zip-path=${GITHUB_WORKSPACE}/${SLUG}.zip" >> "${GITHUB_OUTPUT}"
echo "✓ Zip file generated!"